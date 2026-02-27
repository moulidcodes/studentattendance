<?php
// login.php — simple secure login page
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token    = $_POST['csrf_token'] ?? null;

    if (!verify_csrf($token)) {
        flash('Invalid CSRF token', 'error');
        redirect('login.php');
    }

    if ($username === '' || $password === '') {
        flash('Username and password required', 'error');
        redirect('login.php');
    }

    $db = getDb();
    $stmt = $db->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :u LIMIT 1");
    $stmt->bindValue(':u', $username);
    $res = $stmt->execute();

    // fetch row via the shim/pdo wrapper or sqlite3
    $row = null;
    if (is_object($res) && method_exists($res, 'fetchArray')) {
        $row = $res->fetchArray(SQLITE3_ASSOC);
    } elseif ($res instanceof PDOStatement ?? false) {
        $row = $res->fetch(PDO::FETCH_ASSOC);
    } elseif (is_array($res)) {
        $row = $res;
    }

    // Fallback: attempt querySingle
    if (!$row) {
        $row = $db->querySingle("SELECT id, username, password_hash, role FROM users WHERE username = '" . $db->escapeString($username) . "'", true);
    }

    if ($row && password_verify($password, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['username'] = $row['username'];
        $_SESSION['role']     = $row['role'];
        flash('Logged in successfully.');
        redirect('index.php');
    } else {
        flash('Invalid username or password.', 'error');
        redirect('login.php');
    }
}

$token = csrf_token();
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Login — Attendance Tracker</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-box">
    <h2>Sign in</h2>
    <?php if ($f = getFlash()): ?>
        <div class="flash <?= esc($f['type']) ?>"><?= esc($f['msg']) ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= esc($token) ?>">
        <div class="form-group">
            <label>Username</label>
            <input name="username" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div style="display:flex;gap:8px">
            <button class="btn btn-primary" type="submit">Sign in</button>
            <a class="btn btn-ghost" href="index.php">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
