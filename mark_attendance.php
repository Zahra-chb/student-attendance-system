
<?php
session_start();
require_once "db_connect.php";
if($_SESSION['role']!="professor") { header("Location: login.php"); exit; }

$session_id = $_GET['session_id'];
// Charger session info et étudiants du groupe
$session = $conn->query("SELECT * FROM attendance_sessions WHERE id=$session_id")->fetch();
$students = $conn->query("SELECT * FROM students WHERE group_id=".$session['group_id'])->fetchAll();

if($_SERVER['REQUEST_METHOD']=="POST"){
    foreach($students as $s){
        $status = $_POST['status'][$s['id']] ?? 'absent';
        $stmt = $conn->prepare("INSERT INTO attendance_records(session_id,student_id,status) VALUES(?,?,?)");
        $stmt->execute([$session_id,$s['id'],$status]);
    }
    echo "Attendance saved!";
}
?>
<form method="post">
<table border="1">
<tr><th>Student</th><th>Status</th></tr>
<?php foreach($students as $s): ?>
<tr>
    <td><?= $s['fullname'] ?></td>
    <td>
        <select name="status[<?= $s['id'] ?>]">
            <option value="present">Present</option>
            <option value="absent">Absent</option>
        </select>
    </td>
</tr>
<?php endforeach; ?>
</table>
<button type="submit">Save Attendance</button>
</form>
