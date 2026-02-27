<?php
// includes/layout_header.php — Top of every page (HTML head + sidebar + topbar open)
/** @var string $pageTitle — set before including this file */
/** @var string $action    — current page action */

$navItems = [
    'dashboard'       => ['icon' => '📊', 'label' => 'Dashboard'],
    'take_attendance' => ['icon' => '✅', 'label' => 'Take Attendance'],
    'students'        => ['icon' => '👥', 'label' => 'Students'],
    'reports'         => ['icon' => '📋', 'label' => 'Reports'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($pageTitle ?? 'Attendance Tracker') ?> — Attendance Tracker</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="logo">
        <div class="logo-title">Attendance<br>Tracker</div>
        <div class="logo-sub">Academic Portal</div>
    </div>
    <nav>
        <?php foreach ($navItems as $key => $item): ?>
        <a href="index.php?action=<?= $key ?>" class="<?= ($action ?? '') === $key ? 'active' : '' ?>">
            <span class="icon"><?= $item['icon'] ?></span> <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-bottom">
        <div class="sidebar-date"><?= date('l, F j, Y') ?></div>
    </div>
</aside>

<!-- Main -->
<div class="main">
    <div class="topbar">
        <div class="page-title"><?= esc($pageTitle ?? '') ?></div>
        <div style="display:flex;gap:12px;align-items:center">
            <a href="index.php?action=take_attendance" class="btn btn-primary">✅&nbsp; Take Attendance</a>
            <?php if (!empty($_SESSION['username'])): ?>
                <div style="font-size:.9rem;color:var(--muted)">Signed in as <strong><?= esc($_SESSION['username']) ?></strong></div>
                <a href="logout.php" class="btn btn-ghost">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-ghost">Login</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="content">

<?php
// Flash message
$flash = getFlash();
if ($flash): ?>
<div class="flash <?= esc($flash['type']) ?>">
    <?= $flash['type'] === 'success' ? '✓' : '✕' ?> <?= esc($flash['msg']) ?>
</div>
<?php endif; ?>
