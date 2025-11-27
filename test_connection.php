<?php
require_once "db_connect.php";

$conn = getConnection();

if ($conn) {
    echo "<h2>Connection successful ✔</h2>";
} else {
    echo "<h2>Connection failed ❌</h2>";
}
