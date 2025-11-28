<?php
session_start();
require_once "db_connect.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] != "professor") {
    header("Location: login.php");
    exit();
}
include "navbar.php";

$prof_id = $_SESSION['user_id'];

// Récupérer les cours du professeur
$courses_query = $conn->prepare("
    SELECT c.id, c.name,
           COUNT(s.id) as session_count,
           SUM(CASE WHEN s.status='open' THEN 1 ELSE 0 END) as open_sessions
    FROM courses c 
    LEFT JOIN attendance_sessions s ON c.id = s.course_id AND s.prof_id = ?
    GROUP BY c.id
    ORDER BY c.name
");
$courses_query->bind_param("i", $prof_id);
$courses_query->execute();
$courses = $courses_query->get_result();

// Récupérer les sessions récentes
$recent_sessions = $conn->prepare("
    SELECT s.*, c.name as course_name,
           COUNT(ar.id) as record_count,
           SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) as present_count
    FROM attendance_sessions s 
    LEFT JOIN courses c ON s.course_id = c.id 
    LEFT JOIN attendance_records ar ON s.id = ar.session_id
    WHERE s.prof_id = ?
    GROUP BY s.id
    ORDER BY s.created_at DESC 
    LIMIT 5
");
$recent_sessions->bind_param("i", $prof_id);
$recent_sessions->execute();
$recent = $recent_sessions->get_result();

// Stats
$total_sessions = $conn->query("SELECT COUNT(*) as cnt FROM attendance_sessions WHERE prof_id = $prof_id")->fetch_assoc()['cnt'];
$open_sessions = $conn->query("SELECT COUNT(*) as cnt FROM attendance_sessions WHERE prof_id = $prof_id AND status='open'")->fetch_assoc()['cnt'];
$total_courses = $courses->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Dashboard</title>
    <link rel="stylesheet" href="serie1.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<main class="container">
    <h1>Professor Dashboard</h1>
    
    <!-- Quick Stats -->
    <section class="card">
        <h2>Quick Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $total_courses ?></h3>
                <p>Courses</p>
            </div>
            <div class="stat-card">
                <h3><?= $total_sessions ?></h3>
                <p>Total Sessions</p>
            </div>
            <div class="stat-card">
                <h3><?= $open_sessions ?></h3>
                <p>Open Sessions</p>
            </div>
            <div class="stat-card">
                <h3><?= $recent->num_rows ?></h3>
                <p>Recent Sessions</p>
            </div>
        </div>
    </section>

    <!-- Courses List -->
    <section class="card">
        <div class="card-header">
            <h2>My Courses</h2>
            <a href="create_session.php" class="btn">Create New Session</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Total Sessions</th>
                    <th>Open Sessions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($course = $courses->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($course['name'] ?? 'Unknown Course') ?></td>
                    <td><?= $course['session_count'] ?></td>
                    <td>
                        <span class="status-badge <?= $course['open_sessions'] > 0 ? 'status-open' : 'status-closed' ?>">
                            <?= $course['open_sessions'] ?> open
                        </span>
                    </td>
                    <td>
                        <a href="prof_sessions.php?course_id=<?= $course['id'] ?>" class="btn btn-sm">View Sessions</a>
                        <a href="prof_summary.php?course_id=<?= $course['id'] ?>" class="btn btn-sm">Summary</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <!-- Recent Sessions -->
    <section class="card">
        <h2>Recent Sessions</h2>
        <?php if($recent->num_rows > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Attendance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $recent->data_seek(0); // Reset pointer
                while($session = $recent->fetch_assoc()): 
                    $attendance_rate = $session['record_count'] > 0 ? 
                        round(($session['present_count'] / $session['record_count']) * 100, 1) : 0;
                    
                    // CORRECTION LIGNE 139 : Vérifier que les valeurs existent
                    $course_name = $session['course_name'] ?? 'Unknown Course';
                    $session_date = $session['date'] ?? 'Unknown Date';
                    $session_status = $session['status'] ?? 'unknown';
                ?>
                <tr>
                    <td><?= htmlspecialchars($course_name) ?></td>
                    <td><?= htmlspecialchars($session_date) ?></td>
                    <td>
                        <span class="status-badge status-<?= $session_status ?>">
                            <?= ucfirst($session_status) ?>
                        </span>
                    </td>
                    <td>
                        <div class="attendance-bar">
                            <div class="attendance-fill" style="width: <?= $attendance_rate ?>%"></div>
                            <span><?= $attendance_rate ?>%</span>
                        </div>
                    </td>
                    <td>
                        <?php if($session_status == 'open'): ?>
                            <a href="take_attendance.php?session_id=<?= $session['id'] ?>" class="btn btn-sm">Take Attendance</a>
                        <?php endif; ?>
                        <a href="view_attendance.php?session_id=<?= $session['id'] ?>" class="btn btn-sm">View</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No recent sessions found.</p>
        <?php endif; ?>
    </section>
</main>

<script>
$(document).ready(function() {
    // Hover effects
    $('.data-table tbody tr').hover(
        function() { $(this).addClass('hovered'); },
        function() { $(this).removeClass('hovered'); }
    );
});
</script>
</body>
</html>