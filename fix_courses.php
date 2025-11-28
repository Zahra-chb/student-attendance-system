<?php
// fix_courses.php - À exécuter une seule fois
session_start();
require_once "db_connect.php";

echo "<h2>Fixing Courses Database...</h2>";

// Vider la table courses
$conn->query("DELETE FROM courses");

// Insérer tous les cours
$courses = [
    'Programmation et Développement Web',
    'Interfaces Homme-Machine', 
    'Systèmes d\'Information Distribués',
    'Systèmes d\'Aide à la Décision',
    'Génie Logiciel',
    'Environnements Virtuels et Simulation'
];

foreach($courses as $course) {
    $stmt = $conn->prepare("INSERT INTO courses (name) VALUES (?)");
    $stmt->bind_param("s", $course);
    $stmt->execute();
    echo "<p>✅ Added: $course</p>";
}

// Vérifier
$result = $conn->query("SELECT * FROM courses");
echo "<h3>Courses in database:</h3>";
while($row = $result->fetch_assoc()) {
    echo "<p>ID: {$row['id']} - {$row['name']}</p>";
}

echo "<h3>🎉 Fix completed! <a href='prof_home.php'>Go to Professor Dashboard</a></h3>";
?>