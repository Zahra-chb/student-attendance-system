<?php
session_start();
require_once "db_connect.php";
if(!isset($_SESSION['role']) || $_SESSION['role']!='professor'){ header("Location: login.php"); exit(); }
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id){
  $stmt = $conn->prepare("UPDATE attendance_sessions SET status='closed' WHERE id=?");
  $stmt->bind_param("i",$id); $stmt->execute();
}
header("Location: list_sessions.php"); exit();
