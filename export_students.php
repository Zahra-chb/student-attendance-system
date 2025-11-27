<?php
require "db_connect.php";

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=students.csv");

$output = fopen("php://output", "w");

fputcsv($output, ["id","firstname","lastname","matricule","group_id"]);

$result = $conn->query("SELECT * FROM students");

while($row = $result->fetch_assoc()){
    fputcsv($output, $row);
}

fclose($output);
exit();
