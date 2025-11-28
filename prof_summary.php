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

// Vérifier si le cours existe et appartient au professeur
$course_query = $conn->prepare("
    SELECT c.* FROM courses c 
    WHERE c.id = ? AND EXISTS (
        SELECT 1 FROM attendance_sessions s 
        WHERE s.course_id = c.id AND s.prof_id = ?
    )
");
$course_query->bind_param("ii", $course_id, $prof_id);
$course_query->execute();
$course = $course_query->get_result()->fetch_assoc();

if (!$course) {
    echo "<div class='container'><div class='card'><p>Course not found or no sessions for this course.</p><a href='prof_home.php' class='btn'>Back to Dashboard</a></div></div>";
    exit();
}

// Récupérer les étudiants avec leurs stats d'attendance
$students_query = $conn->prepare("
    SELECT 
        st.id, st.first_name, st.last_name, st.matricule, st.group_id,
        COUNT(ar.id) as total_sessions,
        SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN ar.status='absent' THEN 1 ELSE 0 END) as absent_count
    FROM students st 
    LEFT JOIN attendance_records ar ON st.id = ar.student_id 
    LEFT JOIN attendance_sessions s ON ar.session_id = s.id AND s.course_id = ? AND s.prof_id = ?
    WHERE EXISTS (
        SELECT 1 FROM attendance_sessions s2 
        WHERE s2.course_id = ? AND s2.prof_id = ? AND s2.group_id = st.group_id
    )
    GROUP BY st.id
    ORDER BY st.group_id, st.last_name, st.first_name
");
$students_query->bind_param("iiii", $course_id, $prof_id, $course_id, $prof_id);
$students_query->execute();
$students = $students_query->get_result();

// Stats globales
$stats_query = $conn->prepare("
    SELECT 
        COUNT(DISTINCT s.id) as total_sessions,
        COUNT(DISTINCT st.id) as total_students,
        SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) as total_present,
        COUNT(ar.id) as total_records
    FROM attendance_sessions s 
    LEFT JOIN students st ON s.group_id = st.group_id
    LEFT JOIN attendance_records ar ON s.id = ar.session_id AND ar.student_id = st.id
    WHERE s.course_id = ? AND s.prof_id = ?
");
$stats_query->bind_param("ii", $course_id, $prof_id);
$stats_query->execute();
$global_stats = $stats_query->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary - <?= htmlspecialchars($course['name']) ?></title>
    <link rel="stylesheet" href="serie1.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<main class="container">
    <div class="page-header">
        <h1><?= htmlspecialchars($course['name']) ?> - Attendance Summary</h1>
        <div>
            <a href="prof_sessions.php?course_id=<?= $course_id ?>" class="btn">View Sessions</a>
            <a href="prof_home.php" class="btn">Back to Dashboard</a>
        </div>
    </div>

    <!-- Global Stats -->
    <section class="card">
        <h2>Course Summary</h2>
        <div class="stats-grid">
            <?php
            $attendance_rate = $global_stats['total_records'] > 0 ? 
                round(($global_stats['total_present'] / $global_stats['total_records']) * 100, 1) : 0;
            ?>
            <div class="stat-card">
                <h3><?= $global_stats['total_sessions'] ?></h3>
                <p>Total Sessions</p>
            </div>
            <div class="stat-card">
                <h3><?= $global_stats['total_students'] ?></h3>
                <p>Total Students</p>
            </div>
            <div class="stat-card">
                <h3><?= $attendance_rate ?>%</h3>
                <p>Overall Attendance</p>
            </div>
            <div class="stat-card">
                <h3><?= $students->num_rows ?></h3>
                <p>Students Tracked</p>
            </div>
        </div>
    </section>

    <!-- Students Attendance Summary -->
    <section class="card">
        <div class="card-header">
            <h2>Students Attendance Details</h2>
            <div class="controls">
                <input type="text" id="searchStudents" placeholder="Search students...">
                <button id="sortAttendance" class="btn btn-sm">Sort by Attendance</button>
            </div>
        </div>

        <?php if($students->num_rows > 0): ?>
        <table class="data-table" id="studentsTable">
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Name</th>
                    <th>Group</th>
                    <th>Sessions Attended</th>
                    <th>Absences</th>
                    <th>Attendance Rate</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($student = $students->fetch_assoc()): 
                    $attendance_rate = $student['total_sessions'] > 0 ? 
                        round(($student['present_count'] / $student['total_sessions']) * 100, 1) : 0;
                    
                    if ($student['total_sessions'] == 0) {
                        $status = 'no-data';
                        $status_text = 'No Data';
                    } elseif ($attendance_rate >= 80) {
                        $status = 'excellent';
                        $status_text = 'Excellent';
                    } elseif ($attendance_rate >= 60) {
                        $status = 'good';
                        $status_text = 'Good';
                    } else {
                        $status = 'poor';
                        $status_text = 'Needs Improvement';
                    }
                ?>
                <tr data-attendance="<?= $attendance_rate ?>">
                    <td><?= htmlspecialchars($student['matricule']) ?></td>
                    <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                    <td>Group <?= $student['group_id'] ?></td>
                    <td><?= $student['present_count'] ?>/<?= $student['total_sessions'] ?></td>
                    <td><?= $student['absent_count'] ?></td>
                    <td>
                        <div class="attendance-bar">
                            <div class="attendance-fill" style="width: <?= $attendance_rate ?>%"></div>
                            <span><?= $attendance_rate ?>%</span>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge status-<?= $status ?>">
                            <?= $status_text ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="empty-state">
                <p>No students found for this course.</p>
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
    $('#searchStudents').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#studentsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Sort by attendance
    $('#sortAttendance').on('click', function() {
        const rows = $('#studentsTable tbody tr').get();
        rows.sort(function(a, b) {
            return $(b).data('attendance') - $(a).data('attendance');
        });
        $('#studentsTable tbody').empty().append(rows);
    });
});
</script>
</body>
</html>