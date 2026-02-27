<?php
$path = realpath(__DIR__ . '/../../attendance.db');
if (!file_exists($path)) { echo "DB not found at $path\n"; exit(1); }
$db = new PDO('sqlite:' . $path);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$rows = $db->query("SELECT id, student_id, date, status, note FROM attendance ORDER BY date DESC");
foreach ($rows as $r) {
    echo $r['id'].'|'.$r['student_id'].'|'.$r['date'].'|'.$r['status'].'|'.$r['note']."\n";
}
