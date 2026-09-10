<?php
session_start();

// Block non-admin users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Admin Dashboard - Shuttle System</title></head>
<body>
    <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    
    <!-- Admin Features -->
    <ul>
        <li><a href="manage_routes.php">Manage Bus Routes & Schedules</a></li>
        <li><a href="view_all_bookings.php">View Student Bookings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</body>
</html>