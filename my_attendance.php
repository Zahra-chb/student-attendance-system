<?php
session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='student'){ header("Location: login.php"); exit(); }
include "navbar.php";
$student_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if(!$course_id){
  header("Location: student_home.php"); exit();
}

// fetch sessions
$stmt = $conn->prepare("SELECT s.id,s.date FROM attendance_sessions s WHERE s.course_id=? ORDER BY s.date");
$stmt->bind_param("i",$course_id);
$stmt->execute(); $sessions = $stmt->get_result();

// handle justification upload
$msg='';
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_FILES['just_file'])){
  $session_id = (int)$_POST['session_id'];
  $f = $_FILES['just_file'];
  if($f['error']===0){
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $target = "uploads/just_".$student_id."_".$session_id.".". $ext;
    move_uploaded_file($f['tmp_name'],$target);
    $stmtu = $conn->prepare("UPDATE attendance_records SET justification_path=? WHERE session_id=? AND student_id=?");
    $stmtu->bind_param("sii",$target,$session_id,$student_id);
    $stmtu->execute();
    $msg = "Justification uploaded.";
  } else $msg = "Upload error.";
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>My Attendance</title></head><body>
<main class="container">
  <h2>Attendance for course</h2>
  <?php if($msg) echo "<p class='ok'>$msg</p>"; ?>
  <table class="table">
    <thead><tr><th>Date</th><th>Status</th><th>Justification</th><th>Upload</th></tr></thead>
    <tbody>
    <?php while($s=$sessions->fetch_assoc()):
      $stmt2 = $conn->prepare("SELECT status,justification_path FROM attendance_records WHERE session_id=? AND student_id=?");
      $stmt2->bind_param("ii",$s['id'],$student_id);
      $stmt2->execute(); $rec = $stmt2->get_result()->fetch_assoc();
      $status = $rec['status'] ?? 'Not marked';
      $just = $rec['justification_path'] ?? '';
    ?>
      <tr>
        <td><?=htmlspecialchars($s['date'])?></td>
        <td><?=htmlspecialchars($status)?></td>
        <td><?= $just ? "<a href='".htmlspecialchars($just)."' target='_blank'>View</a>" : '-' ?></td>
        <td>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="session_id" value="<?=$s['id']?>">
            <input type="file" name="just_file" accept=".pdf,.jpg,.png">
            <button type="submit">Upload</button>
          </form>
        </td>
      </tr>
    <?php endwhile;?>
    </tbody>
  </table>
</main>
</body></html>
