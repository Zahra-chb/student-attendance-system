<?php
session_start();
require_once "db_connect.php";
if($_SESSION['role']!="admin"){
    header("Location: login.php");
    exit();
}

$type = $_GET['type'] ?? 'students';

if($type == 'students') {
    // Export students CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=students_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Matricule', 'Group ID']);
    
    $result = $conn->query("SELECT * FROM students ORDER BY id");
    while($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['email'],
            $row['matricule'],
            $row['group_id']
        ]);
    }
    fclose($output);
    
} elseif($type == 'attendance') {
    // Export attendance CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=attendance_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Session Date', 'Course Name', 'Student Name', 'Matricule', 'Status']);
    
    $result = $conn->query("
        SELECT s.date, c.name as course_name, 
               CONCAT(st.first_name, ' ', st.last_name) as student_name,
               st.matricule, ar.status
        FROM attendance_records ar
        JOIN attendance_sessions s ON ar.session_id = s.id
        JOIN courses c ON s.course_id = c.id
        JOIN students st ON ar.student_id = st.id
        ORDER BY s.date DESC, c.name
    ");
    
    while($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['date'],
            $row['course_name'],
            $row['student_name'],
            $row['matricule'],
            $row['status']
        ]);
    }
    fclose($output);
}
exit();