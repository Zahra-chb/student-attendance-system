<?php
require_once "db_connect.php";

function createUser($username, $password, $role, $conn){
    // Vérifier si l'utilisateur existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if($stmt->num_rows == 0){
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmtInsert = $conn->prepare("INSERT INTO users (username,password,role) VALUES (?,?,?)");
        $stmtInsert->bind_param("sss", $username, $hashed, $role);
        $stmtInsert->execute();
        echo "User $username created ✅<br>";
    } else {
        echo "User $username already exists ⚠️<br>";
    }
}

createUser("admin", "admin123", "admin", $conn);
createUser("prof1", "prof123", "professor", $conn);
createUser("etu1", "student123", "student", $conn);
?>
