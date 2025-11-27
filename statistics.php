<?php
session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){ header("Location: login.php"); exit(); }
include "navbar.php";
$courses = $conn->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC);
$data = [];
foreach($courses as $c){
  $cid = (int)$c['id'];
  $present = $conn->query("SELECT COUNT(*) as cnt FROM attendance_records ar JOIN attendance_sessions s ON ar.session_id=s.id WHERE ar.status='present' AND s.course_id=$cid")->fetch_assoc()['cnt'];
  $absent = $conn->query("SELECT COUNT(*) as cnt FROM attendance_records ar JOIN attendance_sessions s ON ar.session_id=s.id WHERE ar.status='absent' AND s.course_id=$cid")->fetch_assoc()['cnt'];
  $data[] = ['name'=>$c['name'],'present'=>$present,'absent'=>$absent];
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Statistics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head><body>
<main class="container">
  <h2>Statistics</h2>
  <?php foreach($data as $i=>$d): ?>
    <h4><?=htmlspecialchars($d['name'])?></h4>
    <canvas id="chart<?=$i?>" width="300" height="150"></canvas>
    <script>
      new Chart(document.getElementById('chart<?=$i?>'), {
        type: 'pie',
        data: {
          labels: ['Present','Absent'],
          datasets: [{ data: [<?= $d['present'] ?>, <?= $d['absent'] ?>] }]
        }
      });
    </script>
  <?php endforeach; ?>
</main>
</body></html>
