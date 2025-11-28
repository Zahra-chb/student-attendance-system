<?php
// check.php - Vérifier la base de données
session_start();
require_once "db_connect.php";

echo "<h1>🔍 VÉRIFICATION URGENTE</h1>";

// 1. Vérifier les cours
echo "<h2>1. Cours dans la base :</h2>";
$courses = $conn->query("SELECT * FROM courses");
if($courses->num_rows > 0) {
    while($course = $courses->fetch_assoc()) {
        echo "<p>✅ <strong>ID {$course['id']}:</strong> {$course['name']}</p>";
    }
} else {
    echo "<p style='color: red; font-size: 20px;'>❌ AUCUN COURS DANS LA BASE !</p>";
}

// 2. Vérifier les sessions
echo "<h2>2. Sessions existantes :</h2>";
$sessions = $conn->query("SELECT * FROM attendance_sessions");
if($sessions->num_rows > 0) {
    while($session = $sessions->fetch_assoc()) {
        echo "<p>Session: Course {$session['course_id']}, Group {$session['group_id']}, Date {$session['date']}</p>";
    }
} else {
    echo "<p>Aucune session créée</p>";
}

// 3. Test direct
echo "<h2>3. Test direct :</h2>";
$test_id = 1;
$test_course = $conn->query("SELECT * FROM courses WHERE id = $test_id")->fetch_assoc();
if($test_course) {
    echo "<p style='color: green;'>✅ COURS ID 1 EXISTE : {$test_course['name']}</p>";
    echo "<p><a href='prof_sessions.php?course_id=1'>Test: prof_sessions.php?course_id=1</a></p>";
} else {
    echo "<p style='color: red;'>❌ COURS ID 1 N'EXISTE PAS</p>";
}

echo "<h2>4. <a href='prof_home.php'>Retour au Dashboard</a></h2>";
?>