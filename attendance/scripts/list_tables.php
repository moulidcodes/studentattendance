<?php
$path = realpath(__DIR__ . '/../../attendance.db');
if (!file_exists($path)) { echo "DB not found at $path\n"; exit(1); }
$db = new PDO('sqlite:' . $path);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$res = $db->query("SELECT name, type, sql FROM sqlite_master WHERE type IN ('table','index')");
foreach ($res as $r) {
    echo $r['type'] . '|' . $r['name'] . '|' . substr($r['sql'],0,300) . "\n";
}
