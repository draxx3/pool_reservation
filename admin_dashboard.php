<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

echo "<h2>Welcome, Admin</h2>";
echo "<a href='admin_reservations.php'>Manage Reservations</a><br>";
echo "<a href='admin_logout.php'>Logout</a>";
?>
