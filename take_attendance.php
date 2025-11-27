<?php
session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='professor'){ header("Location: login.php"); exit(); }
include "navbar.php";
$session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
if(!$session_id){ echo "No session id."; exit(); }

// session info
$stmt = $conn->prepare("SELECT s.*, c.name as course_name FROM attendance_sessions s LEFT JOIN courses c ON s.course_id=c.id WHERE s.id=?");
$stmt->bind_param("i",$session_id);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();
if(!$session){ echo "Session not found."; exit(); }

// students
$gid = (int)$session['group_id'];
$students = $conn->query("SELECT * FROM students WHERE group_id=$gid ORDER BY last_name");

// handle POST (save)
if($_SERVER['REQUEST_METHOD']=='POST'){
  // delete existing for this session to avoid dupes
  $conn->query("DELETE FROM attendance_records WHERE session_id=$session_id");
  foreach($_POST as $k=>$v){
    if(strpos($k,'status_')===0){
      $sid = (int)str_replace('status_','',$k);
      $status = ($v==='present') ? 'present' : 'absent';
      $ins = $conn->prepare("INSERT INTO attendance_records (session_id,student_id,status) VALUES (?,?,?)");
      $ins->bind_param("iis",$session_id,$sid,$status);
      $ins->execute();
    }
  }
  header("Location: list_sessions.php");
  exit();
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Take Attendance</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head><body>
<main class="container mt-4">
  <h2>Take Attendance — <?=htmlspecialchars($session['course_name'])?> (Group <?=$session['group_id']?>) — <?=$session['date']?></h2>
  <form method="post">
    <table class="table table-striped">
      <thead><tr><th>Matricule</th><th>Full name</th><th>Present</th></tr></thead>
      <tbody>
      <?php while($st=$students->fetch_assoc()):
         $r = $conn->query("SELECT status FROM attendance_records WHERE session_id=$session_id AND student_id=".$st['id'])->fetch_assoc();
         $cur = $r['status'] ?? 'absent';
      ?>
      <tr>
        <td><?=htmlspecialchars($st['matricule'])?></td>
        <td><?=htmlspecialchars($st['first_name'].' '.$st['last_name'])?></td>
        <td>
          <select name="status_<?=$st['id']?>" class="form-select form-select-sm">
            <option value="present" <?= $cur==='present' ? 'selected' : '' ?>>Present</option>
            <option value="absent" <?= $cur!=='present' ? 'selected' : '' ?>>Absent</option>
          </select>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    <button class="btn btn-success w-100">Save Attendance</button>
  </form>
</main>
</body></html>
