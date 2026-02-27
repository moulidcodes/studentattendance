<?php
// The application stores DB at parent of the attendance folder: ../attendance.db
$path = realpath(__DIR__ . '/../../attendance.db');
if (!file_exists($path)) { echo "DB not found at $path\n"; exit(1); }
$db = new PDO('sqlite:' . $path);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->query('SELECT id, student_id, name FROM students');
foreach ($stmt as $row) {
    echo $row['id'] . '|' . $row['student_id'] . '|' . $row['name'] . "\n";
}
