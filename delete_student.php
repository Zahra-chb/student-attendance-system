<?php
$host = "localhost";
$dbname = "attendance_app";
$user = "root";
$pass = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Récupérer l'id depuis l'URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
    $stmt->execute([$id]);
}

// Redirection vers la liste
header("Location: list_students.php");
exit;
