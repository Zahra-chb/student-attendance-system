<?php
session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){ 
    header("Location: login.php"); 
    exit(); 
}
include "navbar.php";

$tot_students = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$tot_prof = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='professor'")->fetch_assoc()['c'];
$tot_sessions = $conn->query("SELECT COUNT(*) as c FROM attendance_sessions")->fetch_assoc()['c'];
$tot_courses = $conn->query("SELECT COUNT(*) as c FROM courses")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="serie1.css">
</head>
<body>
<main class="container">
    <h1>Admin Dashboard</h1>
    
    <!-- Quick Stats -->
    <section class="card">
        <h2>System Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $tot_students ?></h3>
                <p>Total Students</p>
            </div>
            <div class="stat-card">
                <h3><?= $tot_prof ?></h3>
                <p>Total Professors</p>
            </div>
            <div class="stat-card">
                <h3><?= $tot_sessions ?></h3>
                <p>Attendance Sessions</p>
            </div>
            <div class="stat-card">
                <h3><?= $tot_courses ?></h3>
                <p>Total Courses</p>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="card">
        <h2>Quick Actions</h2>
        <div class="quick-actions">
            <a href="list_students.php" class="btn">Manage Students</a>
            <a href="statistics.php" class="btn btn-primary">View Statistics</a>
            <a href="manage_courses.php" class="btn">Manage Courses</a>
            <a href="system_reports.php" class="btn">Generate Reports</a>
           <a href="export_csv.php" class="btn">Export Sudents CSV</a>
           <a href="import_csv.php" class="btn">Import attendance CSV</a>
        

            
        </div>
    </section>
</main>
</body>
</html>