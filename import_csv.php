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

if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if($file['error'] === UPLOAD_ERR_OK) {
        $file_type = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if($file_type !== 'csv') {
            $message = "<div class='alert error'>Please upload a CSV file.</div>";
        } else {
            $handle = fopen($file['tmp_name'], 'r');
            $imported = 0;
            $errors = 0;
            $error_details = [];
            
            // Skip header row
            fgetcsv($handle);
            
            while(($data = fgetcsv($handle)) !== FALSE) {
                if(count($data) >= 5) {
                    $first_name = trim($data[0]);
                    $last_name = trim($data[1]);
                    $email = trim($data[2]);
                    $matricule = trim($data[3]);
                    $group_id = (int)trim($data[4]);
                    
                    // Validation
                    if(empty($first_name) || empty($last_name) || empty($email) || empty($matricule)) {
                        $errors++;
                        $error_details[] = "Missing required fields for: $first_name $last_name";
                        continue;
                    }
                    
                    // Check if student already exists
                    $check = $conn->prepare("SELECT id FROM students WHERE email = ? OR matricule = ?");
                    $check->bind_param("ss", $email, $matricule);
                    $check->execute();
                    
                    if($check->get_result()->num_rows > 0) {
                        $errors++;
                        $error_details[] = "Student already exists: $email or $matricule";
                        continue;
                    }
                    
                    // Insert student
                    $stmt = $conn->prepare("INSERT INTO students (first_name, last_name, email, matricule, group_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $matricule, $group_id);
                    
                    if($stmt->execute()) {
                        $imported++;
                    } else {
                        $errors++;
                        $error_details[] = "Database error for: $first_name $last_name";
                    }
                } else {
                    $errors++;
                    $error_details[] = "Invalid data format in row";
                }
            }
            
            fclose($handle);
            
            if($imported > 0) {
                $message = "<div class='alert success'>Successfully imported $imported students!</div>";
            }
            
            if($errors > 0) {
                $message .= "<div class='alert error'>$errors records failed to import.</div>";
                
                // Show first few errors
                if(count($error_details) > 0) {
                    $message .= "<div class='error-details' style='margin-top: 10px; font-size: 0.9em;'>";
                    $message .= "<strong>Error details (first 5):</strong><br>";
                    for($i = 0; $i < min(5, count($error_details)); $i++) {
                        $message .= "• " . htmlspecialchars($error_details[$i]) . "<br>";
                    }
                    $message .= "</div>";
                }
            }
        }
    } else {
        $message = "<div class='alert error'>Error uploading file. Please try again.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Students - Admin</title>
    <link rel="stylesheet" href="serie1.css">
    <style>
    .form-container {
        max-width: 700px;
        margin: 0 auto;
    }
    .form-group {
        margin-bottom: 25px;
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
    .file-upload {
        border: 2px dashed #d1d5db;
        padding: 40px;
        text-align: center;
        border-radius: 8px;
        background: #f8fafc;
        transition: border-color 0.3s;
    }
    .file-upload:hover {
        border-color: #001125;
    }
    .file-upload.dragover {
        border-color: #001125;
        background: #f0f9ff;
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
    .csv-template {
        background: #f8fafc;
        padding: 20px;
        border-radius: 6px;
        margin-top: 20px;
    }
    .csv-template h4 {
        margin-top: 0;
        color: #001125;
    }
    .download-template {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 16px;
        background: #001125;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 0.9em;
    }
    .download-template:hover {
        background: #000d1f;
    }
    </style>
</head>
<body>
<main class="container">
    <div class="page-header">
        <h1>Import Students from CSV</h1>
        <a href="list_students.php" class="btn">Back to Students</a>
    </div>

    <section class="card">
        <div class="form-container">
            <h2>Import Students</h2>
            
            <?= $message ?>

            <div class="csv-template">
                <h4>📋 CSV Format Template</h4>
                <p>Your CSV file should have the following columns (in this order):</p>
                <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
                    <thead>
                        <tr style="background: #001125; color: white;">
                            <th style="padding: 8px; border: 1px solid #ddd;">first_name</th>
                            <th style="padding: 8px; border: 1px solid #ddd;">last_name</th>
                            <th style="padding: 8px; border: 1px solid #ddd;">email</th>
                            <th style="padding: 8px; border: 1px solid #ddd;">matricule</th>
                            <th style="padding: 8px; border: 1px solid #ddd;">group_id</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">John</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">Doe</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">john.doe@univ.dz</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">2025001</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">1</td>
                        </tr>
                    </tbody>
                </table>
                <a href="download_template.php" class="download-template">📥 Download Template CSV</a>
            </div>

            <form method="post" enctype="multipart/form-data" id="uploadForm">
                <div class="form-group">
                    <label for="csv_file">Select CSV File *</label>
                    <div class="file-upload" id="fileUploadArea">
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required 
                               style="display: none;" onchange="updateFileName()">
                        <div style="margin-bottom: 15px;">
                            <i style="font-size: 2em; color: #001125;">📁</i>
                        </div>
                        <p style="margin-bottom: 15px; color: #666;">
                            Drag & drop your CSV file here or click to browse
                        </p>
                        <button type="button" class="btn" onclick="document.getElementById('csv_file').click()">
                            Choose File
                        </button>
                        <div id="fileName" style="margin-top: 10px; font-weight: 600; color: #001125;"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        📤 Import Students
                    </button>
                    <a href="list_students.php" class="btn" style="flex: 0.5;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </section>
</main>

<script>
function updateFileName() {
    const fileInput = document.getElementById('csv_file');
    const fileName = document.getElementById('fileName');
    
    if(fileInput.files.length > 0) {
        fileName.textContent = 'Selected file: ' + fileInput.files[0].name;
    } else {
        fileName.textContent = '';
    }
}

// Drag and drop functionality
const fileUploadArea = document.getElementById('fileUploadArea');
const fileInput = document.getElementById('csv_file');

fileUploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    fileUploadArea.classList.add('dragover');
});

fileUploadArea.addEventListener('dragleave', () => {
    fileUploadArea.classList.remove('dragover');
});

fileUploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    fileUploadArea.classList.remove('dragover');
    
    if(e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        updateFileName();
    }
});
</script>
</body>
</html>