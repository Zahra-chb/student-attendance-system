<?php
session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){ header("Location: login.php"); exit(); }
include "navbar.php";
$tot_students = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$tot_prof = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='professor'")->fetch_assoc()['c'];
$tot_sessions = $conn->query("SELECT COUNT(*) as c FROM attendance_sessions")->fetch_assoc()['c'];
$tot_courses = $conn->query("SELECT COUNT(*) as c FROM courses")->fetch_assoc()['c'];
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin</title></head><body>
<main class="container">
  <h2>Admin Dashboard</h2>
  <div class="cards">
    <div class="card">Students: <?=$tot_students?></div>
    <div class="card">Professors: <?=$tot_prof?></div>
    <div class="card">Sessions: <?=$tot_sessions?></div>
    <div class="card">Courses: <?=$tot_courses?></div>
  </div>
  <p><a class="btn" href="list_students.php">Manage Students</a> <a class="btn" href="statistics.php">Statistics</a></p>
</main>
</body></html>
