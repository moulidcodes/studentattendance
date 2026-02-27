<?php
// includes/db.php — Database connection & schema

// If the SQLite3 extension isn't available, provide a minimal shim that
// implements the methods the app expects using PDO_SQLITE. This lets the
// project run without requiring the sqlite3 extension at the PHP level.
if (!class_exists('SQLite3')) {
    if (!defined('SQLITE3_ASSOC')) define('SQLITE3_ASSOC', PDO::FETCH_ASSOC);

    class SQLite3 {
        private $pdo;
        public function __construct(string $file) {
            $this->pdo = new PDO('sqlite:' . $file);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        public function busyTimeout(int $ms): void { /* no-op for PDO */ }
        public function exec(string $sql) {
            return $this->pdo->exec($sql);
        }
        public function prepare(string $sql) {
            return new class($this->pdo, $sql) {
                private $pdo;
                private $sql;
                private $params = [];
                public function __construct($pdo, $sql) { $this->pdo = $pdo; $this->sql = $sql; }
                public function bindValue($k, $v) { $this->params[$k] = $v; }
                public function execute() {
                    $stmt = $this->pdo->prepare($this->sql);
                    foreach ($this->params as $k => $v) {
                        // strip leading ':' if present
                        $key = ltrim($k, ':');
                        $stmt->bindValue(':' . $key, $v);
                    }
                    $stmt->execute();
                    return $stmt;
                }
                public function reset() { $this->params = []; }
            };
        }
        public function query(string $sql) {
            $stmt = $this->pdo->query($sql);
            return new class($stmt) {
                private $stmt;
                public function __construct($stmt) { $this->stmt = $stmt; }
                public function fetchArray($mode = SQLITE3_ASSOC) {
                    return $this->stmt->fetch(PDO::FETCH_ASSOC);
                }
            };
        }
        public function querySingle(string $sql, bool $entireRow = false) {
            $stmt = $this->pdo->query($sql);
            if ($entireRow) return $stmt->fetch(PDO::FETCH_ASSOC);
            $row = $stmt->fetch(PDO::FETCH_NUM);
            return $row ? (int)$row[0] : 0;
        }
        public function escapeString(string $s): string {
            // basic escaping for literals in SQL statements
            return str_replace("'", "''", $s);
        }
        public function close(): void { $this->pdo = null; }
    }
}

define('DB_FILE', __DIR__ . '/../attendance.db');

function getDb(): SQLite3 {
    static $db = null;
    if ($db === null) {
        $db = new SQLite3(DB_FILE);
        $db->busyTimeout(5000);
        $db->exec("PRAGMA journal_mode=WAL;");
        createSchema($db);
        seedData($db);
    }
    return $db;
}

function createSchema(SQLite3 $db): void {
    $db->exec("
        CREATE TABLE IF NOT EXISTS students (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            name        TEXT NOT NULL,
            student_id  TEXT NOT NULL UNIQUE,
            class       TEXT NOT NULL,
            created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS attendance (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id  INTEGER NOT NULL,
            date        TEXT NOT NULL,
            status      TEXT NOT NULL CHECK(status IN ('present','absent','late','excused')),
            note        TEXT DEFAULT '',
            created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(student_id, date),
            FOREIGN KEY(student_id) REFERENCES students(id)
        );
        
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");
}

function seedData(SQLite3 $db): void {
    if ($db->querySingle("SELECT COUNT(*) FROM students") > 0) return;

    // seed admin user if none
    if ($db->querySingle("SELECT COUNT(*) FROM users") == 0) {
        $hash = password_hash('admin', PASSWORD_DEFAULT);
        $stmtu = $db->prepare("INSERT INTO users(username, password_hash, role) VALUES(:u, :p, :r)");
        $stmtu->bindValue(':u', 'admin');
        $stmtu->bindValue(':p', $hash);
        $stmtu->bindValue(':r', 'admin');
        $stmtu->execute();
    }

    $samples = [
        ['Alice Johnson',  'S001', 'Grade 10-A'],
        ['Bob Martinez',   'S002', 'Grade 10-A'],
        ['Carol White',    'S003', 'Grade 10-A'],
        ['David Kim',      'S004', 'Grade 10-B'],
        ['Emma Davis',     'S005', 'Grade 10-B'],
        ['Frank Wilson',   'S006', 'Grade 10-B'],
    ];
    $stmt = $db->prepare("INSERT INTO students(name, student_id, class) VALUES(:n, :s, :c)");
    foreach ($samples as [$n, $s, $c]) {
        $stmt->bindValue(':n', $n);
        $stmt->bindValue(':s', $s);
        $stmt->bindValue(':c', $c);
        $stmt->execute();
        $stmt->reset();
    }
}
