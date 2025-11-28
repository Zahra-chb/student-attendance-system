<?php
session_start();
require_once "db_connect.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] != "professor") {
    header("Location: login.php");
    exit();
}
include "navbar.php";

$prof_id = $_SESSION['user_id'];

// Récupérer TOUTES les sessions du professeur groupées par cours
$sessions_query = $conn->prepare("
    SELECT s.*, c.name as course_name, c.id as course_id,
           COUNT(ar.id) as total_records,
           SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) as present_count
    FROM attendance_sessions s 
    LEFT JOIN courses c ON s.course_id = c.id 
    LEFT JOIN attendance_records ar ON s.id = ar.session_id
    WHERE s.prof_id = ?
    GROUP BY s.id
    ORDER BY c.name, s.date DESC
");
$sessions_query->bind_param("i", $prof_id);
$sessions_query->execute();
$sessions = $sessions_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Sessions</title>
    <link rel="stylesheet" href="serie1.css">
</head>
<body>
<main class="container">
    <div class="page-header">
        <h1>My Sessions</h1>
        <a href="prof_home.php" class="btn">Back to Dashboard</a>
        <a href="create_session.php" class="btn btn-primary">Create New Session</a>
    </div>

    <?php if($sessions->num_rows > 0): ?>
    
    <?php 
    // Stocker toutes les sessions dans un tableau groupé par cours
    $sessions_by_course = [];
    while($session = $sessions->fetch_assoc()) {
        $course_name = $session['course_name'];
        if(!isset($sessions_by_course[$course_name])) {
            $sessions_by_course[$course_name] = [];
        }
        $sessions_by_course[$course_name][] = $session;
    }
    
    // Afficher par cours
    foreach($sessions_by_course as $course_name => $course_sessions):
        $first_session = $course_sessions[0];
    ?>
        <section class="card">
            <div class="card-header">
                <h2><?= htmlspecialchars($course_name) ?></h2>
                <a href="prof_sessions.php?course_id=<?= $first_session['course_id'] ?>" class="btn btn-sm">View All Sessions</a>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th>Attendance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($course_sessions as $session): 
                        $attendance_rate = $session['total_records'] > 0 ? 
                            round(($session['present_count'] / $session['total_records']) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($session['date']) ?></td>
                        <td>Group <?= htmlspecialchars($session['group_id']) ?></td>
                        <td>
                            <span class="status-badge status-<?= $session['status'] ?>">
                                <?= ucfirst($session['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if($attendance_rate > 0): ?>
                                <div class="attendance-bar">
                                    <div class="attendance-fill" style="width: <?= $attendance_rate ?>%"></div>
                                    <span><?= $attendance_rate ?>%</span>
                                </div>
                            <?php else: ?>
                                <span style="color: #666;">Not taken</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if($session['status'] == 'open'): ?>
                                    <a href="take_attendance.php?session_id=<?= $session['id'] ?>" class="btn btn-sm">Take Attendance</a>
                                <?php endif; ?>
                                <a href="view_attendance.php?session_id=<?= $session['id'] ?>" class="btn btn-sm">View</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endforeach; ?>
    
    <?php else: ?>
        <section class="card">
            <p>No sessions found.</p>
            <a href="create_session.php" class="btn btn-primary">Create First Session</a>
        </section>
    <?php endif; ?>
</main>

<script>
$(document).ready(function() {
    $('.data-table tbody tr').hover(
        function() { $(this).addClass('hovered'); },
        function() { $(this).removeClass('hovered'); }
    );
});
</script>
</body>
</html>