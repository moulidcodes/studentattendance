<?php
// includes/queries.php — All database query functions

function getStudents(SQLite3 $db, string $class = ''): array {
    $where = $class ? "WHERE class = '" . $db->escapeString($class) . "'" : '';
    $result = $db->query("SELECT * FROM students $where ORDER BY class, name");
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

function getStudentById(SQLite3 $db, int $id): ?array {
    $row = $db->querySingle("SELECT * FROM students WHERE id = $id", true);
    return $row ?: null;
}

function getClasses(SQLite3 $db): array {
    $result = $db->query("SELECT DISTINCT class FROM students ORDER BY class");
    $out = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $out[] = $row['class'];
    }
    return $out;
}

function getAttendanceForDate(SQLite3 $db, string $date): array {
    $date = $db->escapeString($date);
    $result = $db->query("SELECT student_id, status, note FROM attendance WHERE date = '$date'");
    $out = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $out[$row['student_id']] = $row;
    }
    return $out;
}

function getStudentStats(SQLite3 $db, int $sid): array {
    $result = $db->query(
        "SELECT status, COUNT(*) as cnt FROM attendance WHERE student_id = $sid GROUP BY status"
    );
    $out = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $out[$row['status']] = (int)$row['cnt'];
    }
    return $out;
}

function getStudentHistory(SQLite3 $db, int $sid, int $limit = 60): array {
    $result = $db->query(
        "SELECT date, status, note FROM attendance WHERE student_id = $sid ORDER BY date DESC LIMIT $limit"
    );
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

function getDashboardStats(SQLite3 $db): array {
    $today   = date('Y-m-d');
    $total   = (int)$db->querySingle("SELECT COUNT(*) FROM students");
    $present = (int)$db->querySingle("SELECT COUNT(*) FROM attendance WHERE date='$today' AND status='present'");
    $absent  = (int)$db->querySingle("SELECT COUNT(*) FROM attendance WHERE date='$today' AND status='absent'");
    $late    = (int)$db->querySingle("SELECT COUNT(*) FROM attendance WHERE date='$today' AND status='late'");
    $days    = (int)$db->querySingle("SELECT COUNT(DISTINCT date) FROM attendance");
    return compact('total', 'present', 'absent', 'late', 'days');
}

function getRecentActivity(SQLite3 $db, int $limit = 20): array {
    $result = $db->query("
        SELECT a.date, s.name, s.student_id AS sid, a.status, a.note
        FROM attendance a
        JOIN students s ON s.id = a.student_id
        ORDER BY a.date DESC, s.name
        LIMIT $limit
    ");
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

function getAllStudentsSummary(SQLite3 $db): array {
    $students = getStudents($db);
    foreach ($students as &$s) {
        $stats        = getStudentStats($db, $s['id']);
        $total        = array_sum($stats);
        $s['stats']   = $stats;
        $s['total']   = $total;
        $s['pct']     = $total > 0 ? round($stats['present'] / $total * 100) : 0;
    }
    return $students;
}
