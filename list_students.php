<?php
session_start();
require_once "db_connect.php";
if($_SESSION['role']!="admin"){
    header("Location: login.php");
    exit();
}
include "navbar.php";

// Récupérer tous les étudiants avec les noms des groupes
$result = $conn->query("
    SELECT s.*, g.name as group_name 
    FROM students s 
    LEFT JOIN user_groups g ON s.group_id = g.id 
    ORDER BY s.id ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin</title>
    <link rel="stylesheet" href="serie1.css">
</head>
<body>
<main class="container">
    <div class="page-header">
        <h1>Manage Students</h1>
        <a href="admin_home.php" class="btn">Back to Dashboard</a>
        <a href="add_student.php" class="btn btn-primary">Add New Student</a>
    </div>

    <section class="card">
        <h2>Students List</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Matricule</th>
                    <th>Group</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong>
                    </td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <span class="matricule-badge"><?= $row['matricule'] ?></span>
                    </td>
                    <td>
                        <?php if($row['group_name']): ?>
                            <span class="group-badge">Group <?= $row['group_name'] ?></span>
                        <?php else: ?>
                            <span style="color: #666;">No group</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="edit_student.php?id=<?= $row['id'] ?>" class="btn btn-sm">Edit</a>
                            <a href="delete_student.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <?php if($result->num_rows == 0): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <p>No students found in the system.</p>
                <a href="add_student.php" class="btn btn-primary">Add First Student</a>
            </div>
        <?php endif; ?>
    </section>
</main>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.matricule-badge {
    background: #e2e8f0;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.9em;
}

.group-badge {
    background: #001125;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9em;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.85em;
}

.btn-danger {
    background: #dc2626;
    border-color: #dc2626;
}

.btn-danger:hover {
    background: #b91c1c;
    border-color: #b91c1c;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>
</body>
</html>