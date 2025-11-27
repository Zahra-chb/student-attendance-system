<?php
// navbar.php  — no session_start() here (start session in each page)
$role = $_SESSION['role'] ?? '';
?>
<link rel="stylesheet" href="style.css">
<nav class="navbar">
  <div class="logo">📘 AttendanceApp</div>
  <div class="links">
    <?php if($role == 'admin'): ?>
      <a href="admin_home.php">Home</a>
      <a href="add_student.php">Add Student</a>
      <a href="list_students.php">Students</a>
      <a href="import_students.php">Import</a>
      <a href="export_students.php">Export</a>
      <a href="statistics.php">Statistics</a>
    <?php elseif($role == 'professor'): ?>
      <a href="prof_home.php">Home</a>
      <a href="create_session.php">Create Session</a>
      <a href="list_sessions.php">My Sessions</a>
    <?php elseif($role == 'student'): ?>
      <a href="student_home.php">Home</a>
      <a href="my_attendance.php">My Attendance</a>
    <?php endif; ?>
  </div>
  <div class="right"><a href="logout.php">Logout</a></div>
</nav>
<style>
.navbar{background:#001125;color:#fff;display:flex;align-items:center;padding:10px 16px}
.navbar .logo{font-weight:700;margin-right:20px}
.navbar .links a{color:#fff;margin-right:12px;text-decoration:none}
.navbar .right{margin-left:auto}
</style>
