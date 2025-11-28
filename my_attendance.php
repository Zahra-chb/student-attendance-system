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

// ⭐⭐ MODIFICATION : Si aucun cours spécifié, montrer la liste des cours ⭐⭐
if($course_id == 0) {
    // Récupérer tous les cours de l'étudiant
    $courses_query = $conn->prepare("
        SELECT c.id, c.name,
               COUNT(s.id) as session_count,
               SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) as present_count
        FROM courses c 
        JOIN student_courses sc ON c.id = sc.course_id
        LEFT JOIN attendance_sessions s ON c.id = s.course_id AND s.group_id = (SELECT group_id FROM students WHERE id = ?)
        LEFT JOIN attendance_records ar ON s.id = ar.session_id AND ar.student_id = ?
        WHERE sc.student_id = ?
        GROUP BY c.id
        ORDER BY c.name
    ");
    $courses_query->bind_param("iii", $student_id, $student_id, $student_id); // ← 3 paramètres
    $courses_query->execute();
    $courses = $courses_query->get_result();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Attendance</title>
        <link rel="stylesheet" href="serie1.css">
        <style>
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .course-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #001125;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .course-card h3 {
            margin: 0 0 10px 0;
            color: #001125;
        }
        </style>
    </head>
    <body>
    <main class="container">
        <div class="page-header">
            <h1>My Attendance</h1>
            <a href="student_home.php" class="btn">Back to Dashboard</a>
        </div>

        <section class="card">
            <h2>Select a Course to View Attendance</h2>
            <div class="courses-grid">
                <?php while($course = $courses->fetch_assoc()): 
                    $attendance_rate = $course['session_count'] > 0 ? 
                        round(($course['present_count'] / $course['session_count']) * 100, 1) : 0;
                ?>
                <div class="course-card">
                    <h3><?= htmlspecialchars($course['name']) ?></h3>
                    <p>Sessions: <?= $course['session_count'] ?></p>
                    <p>Attendance: <?= $attendance_rate ?>%</p>
                    <a href="view_attendance.php?course_id=<?= $course['id'] ?>" class="btn btn-primary">View Attendance</a>
                </div>
                <?php endwhile; ?>
            </div>
            
            <?php if($courses->num_rows == 0): ?>
                <p>You are not enrolled in any courses yet.</p>
            <?php endif; ?>
        </section>
    </main>
    </body>
    </html>
    <?php
    exit(); // Arrêter l'exécution ici
}

// ⭐⭐ CORRECTION : Changer la vérification du cours pour utiliser student_courses ⭐⭐
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
    echo "<div class='container'><p>Course not found or you are not enrolled.</p><a href='my_attendance.php'>Back to Courses</a></div>";
    exit();
}

// Le reste de votre code...