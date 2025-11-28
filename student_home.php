<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='student'){ 
    header("Location: login.php"); 
    exit(); 
}

include "navbar.php";
$student_id = $_SESSION['user_id'];

// Récupérer les cours où l'étudiant est inscrit (via student_courses)
$courses_query = $conn->prepare("
    SELECT c.id, c.name,
           COUNT(s.id) as session_count,
           SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) as present_count,
           SUM(CASE WHEN ar.status='absent' THEN 1 ELSE 0 END) as absent_count
    FROM courses c 
    JOIN student_courses sc ON c.id = sc.course_id
    LEFT JOIN attendance_sessions s ON c.id = s.course_id AND s.group_id = (SELECT group_id FROM students WHERE id = ?)
    LEFT JOIN attendance_records ar ON s.id = ar.session_id AND ar.student_id = ?
    WHERE sc.student_id = ?
    GROUP BY c.id
    ORDER BY c.name
");
$courses_query->bind_param("iii", $student_id, $student_id, $student_id);
$courses_query->execute();
$courses = $courses_query->get_result();

// Récupérer les absences récentes
$recent_absences = $conn->prepare("
    SELECT ar.*, s.date, c.name as course_name 
    FROM attendance_records ar
    JOIN attendance_sessions s ON ar.session_id = s.id
    JOIN courses c ON s.course_id = c.id
    WHERE ar.student_id = ? AND ar.status = 'absent'
    ORDER BY s.date DESC 
    LIMIT 5
");
$recent_absences->bind_param("i", $student_id);
$recent_absences->execute();
$absences = $recent_absences->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="serie1.css">
</head>
<body>
<main class="container">
    <h1>Student Dashboard</h1>
    
    <!-- Quick Stats -->
    <section class="card">
        <h2>My Overview</h2>
        <div class="stats-grid">
            <?php
            $total_sessions = 0;
            $total_present = 0;
            $total_absent = 0;
            
            $courses->data_seek(0);
            while($course = $courses->fetch_assoc()) {
                $total_sessions += $course['session_count'];
                $total_present += $course['present_count'];
                $total_absent += $course['absent_count'];
            }
            $attendance_rate = $total_sessions > 0 ? round(($total_present / $total_sessions) * 100, 1) : 0;
            ?>
            <div class="stat-card">
                <h3><?= $courses->num_rows ?></h3>
                <p>Enrolled Courses</p>
            </div>
            <div class="stat-card">
                <h3><?= $total_sessions ?></h3>
                <p>Total Sessions</p>
            </div>
            <div class="stat-card">
                <h3><?= $attendance_rate ?>%</h3>
                <p>Overall Attendance</p>
            </div>
            <div class="stat-card">
                <h3><?= $total_absent ?></h3>
                <p>Total Absences</p>
            </div>
        </div>
    </section>

    <!-- My Courses -->
    <section class="card">
        <h2>My Courses</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Sessions</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Attendance Rate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $courses->data_seek(0);
                while($course = $courses->fetch_assoc()): 
                    $course_rate = $course['session_count'] > 0 ? 
                        round(($course['present_count'] / $course['session_count']) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($course['name']) ?></td>
                    <td><?= $course['session_count'] ?></td>
                    <td><?= $course['present_count'] ?></td>
                    <td><?= $course['absent_count'] ?></td>
                    <td>
                        <div class="attendance-bar">
                            <div class="attendance-fill" style="width: <?= $course_rate ?>%"></div>
                            <span><?= $course_rate ?>%</span>
                        </div>
                    </td>
                   <td>
    <a href="view_attendance.php?course_id=<?= $course['id'] ?>" class="btn btn-sm">View Attendance</a>
</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <!-- Recent Absences -->
    <section class="card">
        <h2>Recent Absences</h2>
        <?php if($absences->num_rows > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Justification</th>
                </tr>
            </thead>
            <tbody>
                <?php while($absence = $absences->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($absence['course_name']) ?></td>
                    <td><?= htmlspecialchars($absence['date']) ?></td>
                    <td>
                        <span class="status-badge status-absent">Absent</span>
                    </td>
                    <td>
                        <?php if($absence['justification_path']): ?>
                            <a href="<?= htmlspecialchars($absence['justification_path']) ?>" target="_blank">View</a>
                        <?php else: ?>
                            <span style="color: #666;">No justification</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No recent absences. Great job! 🎉</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>