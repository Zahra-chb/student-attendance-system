<?php
// Afficher toutes les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $matricule = trim($_POST['matricule']);
    $group_id = trim($_POST['group_id']);

    if ($fullname && $matricule && $group_id) {
        $stmt = $conn->prepare("UPDATE students SET fullname=?, matricule=?, group_id=? WHERE id=?");
        $stmt->execute([$fullname, $matricule, $group_id, $id]);

        echo "<p style='color:green;'>Student updated successfully!</p>";
    } else {
        echo "<p style='color:red;'>Please fill all fields.</p>";
    }
}

// Récupérer les données actuelles de l'étudiant
$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Update Student</h2>

<form method="POST" action="">
    Fullname: <input type="text" name="fullname" value="<?= htmlspecialchars($student['fullname']) ?>" required><br><br>
    Matricule: <input type="text" name="matricule" value="<?= htmlspecialchars($student['matricule']) ?>" required><br><br>
    Group: <input type="text" name="group_id" value="<?= htmlspecialchars($student['group_id']) ?>" required><br><br>
    <button type="submit">Update student</button>
</form>

<p><a href="list_students.php">Back to Student List</a></p>

</body>
</html>
