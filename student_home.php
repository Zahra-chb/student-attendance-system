<?php
session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='student'){ header("Location: login.php"); exit(); }
include "navbar.php";
$student_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT DISTINCT s.course_id, c.name FROM attendance_records ar JOIN attendance_sessions s ON ar.session_id=s.id JOIN courses c ON s.course_id=c.id WHERE ar.student_id=?");
$stmt->bind_param("i",$student_id);
$stmt->execute(); $courses = $stmt->get_result();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Student Home</title></head><body>
<main class="container">
  <h2>Welcome Student</h2>
  <p>Select a course:</p>
  <ul>
  <?php while($c=$courses->fetch_assoc()): ?>
    <li><a href="my_attendance.php?course_id=<?=$c['course_id']?>"><?=htmlspecialchars($c['name'])?></a></li>
  <?php endwhile;?>
  </ul>
</main>
</body></html>
