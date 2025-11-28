<?php
session_start();
require_once "db_connect.php";
if($_SESSION['role']!="admin"){
    header("Location: login.php");
    exit();
}
include "navbar.php";

$message = '';
$groups = $conn->query("SELECT * FROM user_groups ORDER BY id");

if($_SERVER['REQUEST_METHOD']=="POST"){
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $matricule = trim($_POST['matricule']);
    $group_id = (int)$_POST['group_id'];

    // Vérifier si l'email ou matricule existe déjà
    $check = $conn->prepare("SELECT id FROM students WHERE email = ? OR matricule = ?");
    $check->bind_param("ss", $email, $matricule);
    $check->execute();
    
    if($check->get_result()->num_rows > 0){
        $message = "<div class='alert error'>Error: Email or matricule already exists!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO students (first_name,last_name,email,matricule,group_id) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $matricule, $group_id);
        
        if($stmt->execute()){
            $message = "<div class='alert success'>Student added successfully!</div>";
            // Réinitialiser les champs
            $_POST = array();
        } else {
            $message = "<div class='alert error'>Error adding student: " . $conn->error . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Admin</title>
    <link rel="stylesheet" href="serie1.css">
    <style>
    .form-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #001125;
    }
    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 16px;
        transition: border-color 0.3s;
    }
    .form-control:focus {
        outline: none;
        border-color: #001125;
        box-shadow: 0 0 0 3px rgba(0, 17, 37, 0.1);
    }
    .alert {
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
    }
    .alert.success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .alert.error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    </style>
</head>
<body>
<main class="container">
    <div class="page-header">
        <h1>Add New Student</h1>
        <a href="list_students.php" class="btn">Back to Students</a>
    </div>

    <section class="card">
        <div class="form-container">
            <h2>Student Information</h2>
            
            <?= $message ?>

            <form method="post">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" 
                           class="form-control" 
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" 
                           class="form-control" 
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" 
                           class="form-control" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="matricule">Matricule *</label>
                    <input type="text" id="matricule" name="matricule" 
                           class="form-control" 
                           value="<?= htmlspecialchars($_POST['matricule'] ?? '') ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="group_id">Group *</label>
                    <select id="group_id" name="group_id" class="form-control" required>
                        <option value="">Select a group</option>
                        <?php while($group = $groups->fetch_assoc()): ?>
                            <option value="<?= $group['id'] ?>" 
                                <?= (($_POST['group_id'] ?? '') == $group['id']) ? 'selected' : '' ?>>
                                Group <?= htmlspecialchars($group['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        Add Student
                    </button>
                    <a href="list_students.php" class="btn" style="flex: 0.5;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </section>
</main>
</body>
</html>