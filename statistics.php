<?php
session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){ 
    header("Location: login.php"); 
    exit(); 
}
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Admin</title>
    <link rel="stylesheet" href="serie1.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 30px;
        margin: 20px 0;
    }
    .chart-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
    }
    .chart-title {
        margin-bottom: 15px;
        color: #001125;
        font-size: 1.1em;
        font-weight: bold;
    }
    </style>
</head>
<body>
<main class="container">
    <div class="page-header">
        <h1>Attendance Statistics</h1>
        <a href="admin_home.php" class="btn">Back to Dashboard</a>
    </div>

    <section class="card">
        <h2>Course Attendance Overview</h2>
        <div class="charts-grid">
            <?php foreach($data as $i=>$d): ?>
            <div class="chart-container">
                <div class="chart-title"><?= htmlspecialchars($d['name']) ?></div>
                <canvas id="chart<?= $i ?>" width="300" height="200"></canvas>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Detailed Table -->
    <section class="card">
        <h2>Attendance Details</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Total Records</th>
                    <th>Attendance Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data as $d): 
                    $total = $d['present'] + $d['absent'];
                    $rate = $total > 0 ? round(($d['present'] / $total) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($d['name']) ?></td>
                    <td><?= $d['present'] ?></td>
                    <td><?= $d['absent'] ?></td>
                    <td><?= $total ?></td>
                    <td>
                        <div class="attendance-bar">
                            <div class="attendance-fill" style="width: <?= $rate ?>%"></div>
                            <span><?= $rate ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<script>
<?php foreach($data as $i=>$d): ?>
new Chart(document.getElementById('chart<?= $i ?>'), {
    type: 'pie',
    data: {
        labels: ['Present','Absent'],
        datasets: [{ 
            data: [<?= $d['present'] ?>, <?= $d['absent'] ?>],
            backgroundColor: ['#001125', '#dc2626']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endforeach; ?>
</script>
</body>
</html>