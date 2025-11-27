<?php
require "db_connect.php";

$username = "prof1";
$password = password_hash("prof123", PASSWORD_BCRYPT);
$role = "professor";

$stmt = $conn->prepare("INSERT INTO users (username,password,role) VALUES (?,?,?)");
$stmt->bind_param("sss", $username,$password,$role);
$stmt->execute();

echo "Professor created successfully";
?>
