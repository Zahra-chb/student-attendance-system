<?php
session_start();
if(!isset($_SESSION['role'])) header("Location: login.php");
$role = $_SESSION['role'];
if($role=="admin") header("Location: admin_home.php");
elseif($role=="professor") header("Location: prof_home.php");
else header("Location: student_home.php");
exit();

