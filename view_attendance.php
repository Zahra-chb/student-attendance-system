<?php
session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='professor'){ header("Location: login.php"); exit(); }
include "navbar.php";
$sid = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
$stmt = $conn->prepare("SELECT s.*, c.name as course_name FROM attendance_sessions s LEFT JOIN courses c ON s.course_id=c.id WHERE s.id=?");
$stmt->bind_param("i",$sid);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();
if(!$session){ echo "Session not found."; exit(); }
$records = $conn->prepare("SELECT ar.*, st.first_name, st.last_name, st.matricule FROM attendance_records ar JOIN students st ON ar.student_id=st.id WHERE ar.session_id=?");
$records->bind_param("i",$sid);
$records->execute();
$res = $records->get_result();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Session Records</title></head><body>
<main class="container">
  <h2>Session: <?=htmlspecialchars($session['course_name'])?> — <?= $session['date'] ?></h2>
  <table class="table">
    <thead><tr><th>Matricule</th><th>Name</th><th>Status</th></tr></thead>
    <tbody>
    <?php while($r=$res->fetch_assoc()): ?>
      <tr>
        <td><?=htmlspecialchars($r['matricule'])?></td>
        <td><?=htmlspecialchars($r['first_name'].' '.$r['last_name'])?></td>
        <td><?=htmlspecialchars($r['status'])?></td>
      </tr>
    <?php endwhile;?>
    </tbody>
  </table>
  <a class="btn" href="list_sessions.php">Back</a>
</main>
</body></html>
