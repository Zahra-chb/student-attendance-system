<?php
session_start();
require_once "db_connect.php";
if($_SESSION['role']!="admin"){
    header("Location: login.php");
    exit();
}
include "navbar.php";

if($_SERVER['REQUEST_METHOD']=="POST"){
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $matricule = $_POST['matricule'];
    $group_id = $_POST['group_id'];

    $stmt = $conn->prepare("INSERT INTO students (first_name,last_name,email,matricule,group_id) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssii",$first_name,$last_name,$email,$matricule,$group_id);
    $stmt->execute();
    $message = "Student added successfully!";
}
?>
<main class="container">
<h2>Add Student</h2>
<?php if(isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
<form method="post">
<input type="text" name="first_name" placeholder="First Name" required><br>
<input type="text" name="last_name" placeholder="Last Name" required><br>
<input type="email" name="email" placeholder="Email" required><br>
<input type="text" name="matricule" placeholder="Matricule" required><br>
<input type="number" name="group_id" placeholder="Group ID" required><br>
<button type="submit">Add Student</button>
</form>
</main>
