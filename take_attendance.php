<?php
$students_file = 'students.json';
if(!file_exists($students_file)){
    echo "No students found! Please add students first.";
    exit;
}

$students = json_decode(file_get_contents($students_file), true);

$today_file = 'attendance_' . date('Y-m-d') . '.json';

if(isset($_POST['submit'])){
    if(file_exists($today_file)){
        echo "Attendance for today has already been taken.";
        exit;
    }

    $attendance = [];
    foreach($students as $student){
        $status = $_POST['status'][$student['student_id']] ?? 'absent';
        $attendance[] = [
            'student_id' => $student['student_id'],
            'status' => $status
        ];
    }

    file_put_contents($today_file, json_encode($attendance, JSON_PRETTY_PRINT));
    echo "Attendance saved successfully!";
}
?>

<form method="post" action="">
    <?php foreach($students as $student): ?>
        <?php echo $student['name'] . " (" . $student['student_id'] . ")"; ?><br>
        Present <input type="radio" name="status[<?php echo $student['student_id']; ?>]" value="present" checked>
        Absent <input type="radio" name="status[<?php echo $student['student_id']; ?>]" value="absent"><br><br>
    <?php endforeach; ?>
    <input type="submit" name="submit" value="Take Attendance">
</form>
