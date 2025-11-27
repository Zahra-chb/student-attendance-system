<?php
session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='professor'){ header("Location: login.php"); exit(); }
include "navbar.php";
$msg = '';
if($_SERVER['REQUEST_METHOD']=='POST'){
  $course_id = (int)$_POST['course_id'];
  $group_id = (int)$_POST['group_id'];
  $date = $_POST['date'] ?: date('Y-m-d');
  $prof_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("INSERT INTO attendance_sessions (course_id,group_id,date,opened_by,status,prof_id) VALUES (?,?,?,?,?,?)");
  $status='open';
  $opened_by = $_SESSION['user_id'];
  $stmt->bind_param("iisssi",$course_id,$group_id,$date,$opened_by,$status,$prof_id);
  if($stmt->execute()) $msg = "Session created successfully.";
  else $msg = "Error creating session: " . $stmt->error;
}
$courses = $conn->query("SELECT * FROM courses");
$groups = $conn->query("SELECT DISTINCT group_id FROM students");
?>
<!doctype html><html><head><meta charset="utf-8"><title>Create Session</title></head><body>
<main class="container">
  <h2>Create Session</h2>
  <?php if($msg) echo "<p class='ok'>".$msg."</p>"; ?>
  <form method="post">
    <label>Course</label>
    <select name="course_id" required>
      <?php while($c=$courses->fetch_assoc()): ?>
        <option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option>
      <?php endwhile; ?>
    </select><br>
    <label>Group</label>
    <select name="group_id" required>
      <?php while($g=$groups->fetch_assoc()): ?>
        <option value="<?=$g['group_id']?>">Group <?=$g['group_id']?></option>
      <?php endwhile; ?>
    </select><br>
    <label>Date</label>
    <input type="date" name="date" value="<?=date('Y-m-d')?>"><br>
    <button type="submit">Create</button>
  </form>
</main>
</body></html>
