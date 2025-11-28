<?php
session_start();
require_once "db_connect.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] != "professor") {
    header("Location: login.php");
    exit();
}
include "navbar.php";

$prof_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// CORRECTION : Vérifier seulement si le cours existe, pas besoin de sessions
$course_query = $conn->prepare("
    SELECT c.* FROM courses c 
    WHERE c.id = ?
");
$course_query->bind_param("i", $course_id);
$course_query->execute();
$course = $course_query->get_result()->fetch_assoc();

if (!$course) {
    echo "<div class='container'><div class='card'><p>Course not found.</p><a href='prof_home.php' class='btn'>Back to Dashboard</a></div></div>";
    exit();
}

// Récupérer les sessions pour ce cours (même si vide)
$sessions_query = $conn->prepare("
    SELECT s.*, 
           COUNT(ar.id) as total_records,
           SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) as present_count
    FROM attendance_sessions s 
    LEFT JOIN attendance_records ar ON s.id = ar.session_id
    WHERE s.course_id = ? AND s.prof_id = ?
    GROUP BY s.id
    ORDER BY s.date DESC, s.created_at DESC
");
$sessions_query->bind_param("ii", $course_id, $prof_id);
$sessions_query->execute();
$sessions = $sessions_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions - <?= htmlspecialchars($course['name']) ?></title>
    <link rel="stylesheet" href="serie1.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<main class="container">
    <div class="page-header">
        <h1><?= htmlspecialchars($course['name']) ?> - Sessions</h1>
        <a href="prof_home.php" class="btn">Back to Dashboard</a>
    </div>

    <!-- Course Info -->
    <section class="card">
        <div class="course-info">
            <h2>Course Information</h2>
            <div class="info-grid">
                <div><strong>Course Name:</strong> <?= htmlspecialchars($course['name']) ?></div>
                <div><strong>Total Sessions:</strong> <?= $sessions->num_rows ?></div>
                <div>
                    <a href="create_session.php?course_id=<?= $course_id ?>" class="btn btn-primary">Create New Session</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Sessions Table -->
    <section class="card">
        <div class="card-header">
            <h2>Attendance Sessions</h2>
            <div class="controls">
                <input type="text" id="searchSessions" placeholder="Search sessions...">
                <button id="filterOpen" class="btn btn-sm">Open Only</button>
                <button id="filterAll" class="btn btn-sm">All Sessions</button>
            </div>
        </div>
        
        <?php if($sessions->num_rows > 0): ?>
        <table class="data-table" id="sessionsTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Group</th>
                    <th>Status</th>
                    <th>Attendance Rate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($session = $sessions->fetch_assoc()): 
                    $attendance_rate = $session['total_records'] > 0 ? 
                        round(($session['present_count'] / $session['total_records']) * 100, 1) : 0;
                ?>
                <tr data-status="<?= $session['status'] ?>">
                    <td><?= htmlspecialchars($session['date']) ?></td>
                    <td>Group <?= htmlspecialchars($session['group_id']) ?></td>
                    <td>
                        <span class="status-badge status-<?= $session['status'] ?>">
                            <?= ucfirst($session['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="attendance-bar">
                            <div class="attendance-fill" style="width: <?= $attendance_rate ?>%"></div>
                            <span><?= $attendance_rate ?>%</span>
                        </div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <?php if($session['status'] == 'open'): ?>
                                <a href="take_attendance.php?session_id=<?= $session['id'] ?>" class="btn btn-sm btn-primary">Take Attendance</a>
                            <?php endif; ?>
                            <a href="view_attendance.php?session_id=<?= $session['id'] ?>" class="btn btn-sm">View</a>
                            <?php if($session['status'] == 'open'): ?>
                                <a href="close_session.php?id=<?= $session['id'] ?>" class="btn btn-sm btn-warning">Close</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="empty-state">
                <p>No sessions found for this course.</p>
                <a href="create_session.php?course_id=<?= $course_id ?>" class="btn btn-primary">Create First Session</a>
            </div>
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

    // Search functionality
    $('#searchSessions').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#sessionsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Filter functionality
    $('#filterOpen').on('click', function() {
        $('#sessionsTable tbody tr').show();
        $('#sessionsTable tbody tr').each(function() {
            if ($(this).data('status') !== 'open') {
                $(this).hide();
            }
        });
    });

    $('#filterAll').on('click', function() {
        $('#sessionsTable tbody tr').show();
    });
});
</script>
</body>
</html>