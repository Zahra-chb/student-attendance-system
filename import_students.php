<?php
session_start();
require_once "db_connect.php";
if($_SESSION['role']!="admin"){
    header("Location: login.php");
    exit();
}
include "navbar.php";

$message="";
if(isset($_POST['submit'])){
    if(isset($_FILES['csv_file']) && $_FILES['csv_file']['error']==0){
        $file = fopen($_FILES['csv_file']['tmp_name'],'r');
        fgetcsv($file); // ignore header
        while(($row = fgetcsv($file)) !== FALSE){
            $stmt = $conn->prepare("INSERT INTO students (first_name,last_name,email,matricule,group_id) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssii",$row[0],$row[1],$row[2],$row[3],$row[4]);
            $stmt->execute();
        }
        fclose($file);
        $message="Import successful!";
    } else {
        $message="No file selected!";
    }
}
?>
<main class="container">
<h2>Import Students</h2>
<?php if($message) echo "<p style='color:green;'>$message</p>"; ?>
<form method="post" enctype="multipart/form-data">
<input type="file" name="csv_file" required>
<button type="submit" name="submit">Import</button>
</form>
</main>
