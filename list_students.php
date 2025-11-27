<?php
session_start();
require_once "db_connect.php";
if($_SESSION['role']!="admin"){
    header("Location: login.php");
    exit();
}
include "navbar.php";

// Récupérer tous les étudiants
$result = $conn->query("SELECT * FROM students ORDER BY id ASC");
?>
<main class="container">
<h2>Students List</h2>
<table border="1" cellpadding="5">
<tr>
<th>ID</th>
<th>Full Name</th>
<th>Matricule</th>
<th>Group</th>
<th>Actions</th>
</tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
<td><?php echo $row['matricule']; ?></td>
<td><?php echo $row['group_id']; ?></td>
<td>
<a href="edit_student.php?id=<?php echo $row['id']; ?>">Edit</a> |
<a href="delete_student.php?id=<?php echo $row['id']; ?>">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>
<a href="add_student.php">Add New Student</a>
</main>
