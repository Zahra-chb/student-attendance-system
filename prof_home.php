<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != "professor") {
    header("Location: login.php");
    exit();
}
include "navbar.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Professor Dashboard</title>
</head>
<body>
<h1>Welcome Professor</h1>
<p>You can create and manage attendance sessions.</p>
</body>
</html>
