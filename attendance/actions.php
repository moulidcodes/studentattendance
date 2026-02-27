<?php
// actions.php — Handles all POST/GET action processing

function handleAddStudent(SQLite3 $db): void {
    $name = trim($_POST['name']       ?? '');
    $sid  = trim($_POST['student_id'] ?? '');
    $cls  = trim($_POST['class']      ?? '');

    if (!$name || !$sid || !$cls) {
        flash("All fields are required.", 'error');
        redirect("index.php?action=students");
    }

    $stmt = $db->prepare("INSERT INTO students(name, student_id, class) VALUES(:n, :s, :c)");
    $stmt->bindValue(':n', $name);
    $stmt->bindValue(':s', $sid);
    $stmt->bindValue(':c', $cls);

    if ($stmt->execute()) {
        flash("Student \"$name\" added successfully.");
    } else {
        flash("Error: Student ID \"$sid\" already exists.", 'error');
    }
    redirect("index.php?action=students");
}

function handleDeleteStudent(SQLite3 $db): void {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) redirect("index.php?action=students");

    $db->exec("DELETE FROM attendance WHERE student_id = $id");
    $db->exec("DELETE FROM students WHERE id = $id");
    flash("Student removed successfully.");
    redirect("index.php?action=students");
}

function handleSaveAttendance(SQLite3 $db): void {
    $date    = $_POST['date']       ?? date('Y-m-d');
    $records = $_POST['attendance'] ?? [];
    $notes   = $_POST['notes']      ?? [];

    foreach ($records as $sid => $status) {
        $sid    = (int)$sid;
        $status = $db->escapeString($status);
        $note   = $db->escapeString($notes[$sid] ?? '');
        $date_e = $db->escapeString($date);

        $sql = "INSERT INTO attendance(student_id, date, status, note) VALUES($sid, '$date_e', '$status', '$note')"
             . " ON CONFLICT(student_id, date) DO UPDATE SET status = '$status', note = '$note'";
        $db->exec($sql);
    }

    flash("Attendance saved for $date.");
    redirect("index.php?action=take_attendance&date=" . urlencode($date));
}
