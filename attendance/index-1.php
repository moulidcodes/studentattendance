<?php
// index.php — Front controller / router

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/queries.php';
require_once __DIR__ . '/actions.php';

session_start();

$db     = getDb();
$action = $_GET['action'] ?? 'dashboard';

// ── Handle POST/action requests ────────────────────────────────────────────
if ($action === 'add_student'    && $_SERVER['REQUEST_METHOD'] === 'POST') handleAddStudent($db);
if ($action === 'delete_student' && isset($_GET['id']))                     handleDeleteStudent($db);
if ($action === 'save_attendance' && $_SERVER['REQUEST_METHOD'] === 'POST') handleSaveAttendance($db);

// ── Map actions to page files & titles ─────────────────────────────────────
$pages = [
    'dashboard'       => ['file' => 'dashboard.php',       'title' => 'Dashboard Overview'],
    'take_attendance' => ['file' => 'take_attendance.php',  'title' => 'Take Attendance'],
    'students'        => ['file' => 'students.php',         'title' => 'Manage Students'],
    'reports'         => ['file' => 'reports.php',          'title' => 'Attendance Reports'],
];

if (!isset($pages[$action])) {
    header("Location: index.php?action=dashboard");
    exit;
}

$pageTitle = $pages[$action]['title'];
$pageFile  = __DIR__ . '/' . $pages[$action]['file'];

// ── Render ──────────────────────────────────────────────────────────────────
require __DIR__ . '/layout_header.php';
require $pageFile;
require __DIR__ . '/layout_footer.php';

$db->close();
