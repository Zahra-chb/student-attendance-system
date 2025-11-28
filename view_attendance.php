<?php
session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='student'){ 
    header("Location: login.php"); 
    exit(); 
}
include "navbar.php";

$student_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if($course_id == 0) {
    header("Location: student_home.php");
    exit();
}

// Vérifier que l'étudiant est inscrit à ce cours
$course_check = $conn->prepare("
    SELECT c.* FROM courses c 
    JOIN student_courses sc ON c.id = sc.course_id
    WHERE c.id = ? AND sc.student_id = ?
    LIMIT 1
");
$course_check->bind_param("ii", $course_id, $student_id);
$course_check->execute();
$course = $course_check->get_result()->fetch_assoc();

if(!$course){
    echo "<div class='container'><p>Course not found or you are not enrolled.</p><a href='student_home.php'>Back</a></div>";
    exit();
}

// Récupérer toutes les sessions pour ce cours
$sessions_query = $conn->prepare("
    SELECT s.id, s.date, s.group_id,
           ar.status, ar.justification_path,
           CASE 
               WHEN ar.status = 'present' THEN '✅ Present'
               WHEN ar.status = 'absent' THEN '❌ Absent' 
               ELSE '⏳ Not marked'
           END as status_display
    FROM attendance_sessions s 
    LEFT JOIN attendance_records ar ON s.id = ar.session_id AND ar.student_id = ?
    WHERE s.course_id = ? AND s.group_id = (SELECT group_id FROM students WHERE id = ?)
    ORDER BY s.date DESC
");
$sessions_query->bind_param("iii", $student_id, $course_id, $student_id);
$sessions_query->execute();
$sessions = $sessions_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - <?= htmlspecialchars($course['name']) ?></title>
    <link rel="stylesheet" href="serie1.css">
</head>
<body>
<main class="container">
    <div class="page-header">
        <h1><?= htmlspecialchars($course['name']) ?> - Attendance Details</h1>
        <a href="student_home.php" class="btn">Back to Dashboard</a>
    </div>

    <section class="card">
        <h2>Your Attendance Records</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Group</th>
                    <th>Status</th>
                    <th>Justification</th>
                </tr>
            </thead>
            <tbody>
                <?php while($session = $sessions->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($session['date']) ?></td>
                    <td>Group <?= htmlspecialchars($session['group_id']) ?></td>
                    <td>
                        <span class="status-badge status-<?= $session['status'] ?? 'unknown' ?>">
                            <?= $session['status_display'] ?>
                        </span>
                    </td>
                    <td>
                        <?php if($session['justification_path']): ?>
                            <a href="<?= htmlspecialchars($session['justification_path']) ?>" target="_blank">📎 View File</a>
                        <?php else: ?>
                            <span style="color: #666;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>