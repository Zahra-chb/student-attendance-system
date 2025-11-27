<?php
session_start();
require_once "db_connect.php";

if($_SERVER['REQUEST_METHOD']=="POST"){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s",$username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user && password_verify($password,$user['password'])){
        $_SESSION['user_id']=$user['id'];
        $_SESSION['role']=$user['role'];

        if($user['role']=="admin") header("Location: admin_home.php");
        elseif($user['role']=="professor") header("Location: prof_home.php");
        else header("Location: student_home.php");

        exit();
    } else {
        $error="Invalid username or password";
    }
}
?>
<form method="post">
  <input type="text" name="username" placeholder="Username" required><br>
  <input type="password" name="password" placeholder="Password" required><br>
  <button type="submit">Login</button>
</form>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
