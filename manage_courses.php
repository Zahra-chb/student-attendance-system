<?php
session_start();
require_once "db_connect.php";
if($_SESSION['role']!="admin"){
    header("Location: login.php");
    exit();
}
include "navbar.php";

// Récupérer tous les cours (sans group_id puisque la table n'a pas cette colonne)
$courses = $conn->query("SELECT * FROM courses ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admin</title>
    <link rel="stylesheet" href="serie1.css">
</head>
<body>
<main class="container">
    <div class="page-header">
        <h1>Manage Courses</h1>
        <a href="admin_home.php" class="btn">Back to Dashboard</a>
        <a href="add_course.php" class="btn btn-primary">Add New Course</a>
    </div>

    <section class="card">
        <h2>Courses List</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($course = $courses->fetch_assoc()): ?>
                <tr>
                    <td><?= $course['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($course['name']) ?></strong>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="edit_course.php?id=<?= $course['id'] ?>" class="btn btn-sm">Edit</a>
                            <a href="delete_course.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this course?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <?php if($courses->num_rows == 0): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <p>No courses found in the system.</p>
                <a href="add_course.php" class="btn btn-primary">Add First Course</a>
            </div>
        <?php endif; ?>
    </section>
</main>

<style>
.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
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
</style>
</body>
</html>