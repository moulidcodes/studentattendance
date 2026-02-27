<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}
?>

<h2>Admin Dashboard</h2>
<p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
<a href="logout.php">Logout</a>