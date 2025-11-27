<?php
session_start();
require_once "db_connect.php";

if($_SESSION['role'] != "professor"){
    header("Location: login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];

$q = $conn->prepare("SELECT * FROM attendance_sessions WHERE prof_id=?");
$q->bind_param("i", $prof_id);
$q->execute();
$r = $q->get_result();
?>

<h2>My Sessions</h2>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

<?php while($s = $r->fetch_assoc()): ?>
<tr>
    <td><?= $s['id'] ?></td>
    <td><?= $s['created_at'] ?></td>
    <td><?= $s['status'] ?></td>
    <td>
        <a href="mark_attendance.php?session_id=<?= $s['id'] ?>">Mark Attendance</a> |
        <a href="close_session.php?id=<?= $s['id'] ?>">Close</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

<a href="prof_home.php">Back</a>
