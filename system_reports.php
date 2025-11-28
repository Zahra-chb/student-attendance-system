<?php
session_start();
require_once "db_connect.php";
if($_SESSION['role']!="admin"){
    header("Location: login.php");
    exit();
}
include "navbar.php";

// Statistiques pour les rapports
$reports_data = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM students) as total_students,
        (SELECT COUNT(*) FROM courses) as total_courses,
        (SELECT COUNT(*) FROM attendance_sessions) as total_sessions,
        (SELECT COUNT(*) FROM attendance_records WHERE status='present') as total_present,
        (SELECT COUNT(*) FROM attendance_records WHERE status='absent') as total_absent,
        (SELECT COUNT(*) FROM user_groups) as total_groups
")->fetch_assoc();

$attendance_rate = ($reports_data['total_present'] + $reports_data['total_absent']) > 0 ? 
    round(($reports_data['total_present'] / ($reports_data['total_present'] + $reports_data['total_absent'])) * 100, 1) : 0;

// Cours avec le plus bas taux de présence
$low_attendance = $conn->query("
    SELECT c.name,
           COUNT(s.id) as total_sessions,
           SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) as present_count,
           ROUND((SUM(CASE WHEN ar.status='present' THEN 1 ELSE 0 END) / COUNT(s.id)) * 100, 1) as attendance_rate
    FROM courses c 
    LEFT JOIN attendance_sessions s ON c.id = s.course_id 
    LEFT JOIN attendance_records ar ON s.id = ar.session_id 
    GROUP BY c.id 
    HAVING total_sessions > 0
    ORDER BY attendance_rate ASC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports - Admin</title>
    <link rel="stylesheet" href="serie1.css">
</head>
<body>
<main class="container">
    <div class="page-header">
        <h1>System Reports</h1>
        <a href="admin_home.php" class="btn">Back to Dashboard</a>
        <button onclick="window.print()" class="btn btn-primary">Print Report</button>
    </div>

    <section class="card">
        <h2>System Overview Report</h2>
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $reports_data['total_students'] ?></h3>
                <p>Total Students</p>
            </div>
            <div class="stat-card">
                <h3><?= $reports_data['total_courses'] ?></h3>
                <p>Total Courses</p>
            </div>
            <div class="stat-card">
                <h3><?= $reports_data['total_sessions'] ?></h3>
                <p>Attendance Sessions</p>
            </div>
            <div class="stat-card">
                <h3><?= $attendance_rate ?>%</h3>
                <p>Overall Attendance Rate</p>
            </div>
        </div>

        <!-- Detailed Reports -->
        <div class="reports-grid">
            <!-- Low Attendance Courses -->
            <div class="report-card">
                <h3>📊 Low Attendance Courses</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Course Name</th>
                            <th>Sessions</th>
                            <th>Attendance Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($course = $low_attendance->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($course['name']) ?></td>
                            <td><?= $course['total_sessions'] ?></td>
                            <td>
                                <div class="attendance-bar">
                                    <div class="attendance-fill" style="width: <?= $course['attendance_rate'] ?>%"></div>
                                    <span><?= $course['attendance_rate'] ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Attendance Summary -->
            <div class="report-card">
                <h3>📈 Attendance Summary</h3>
                <div class="summary-stats">
                    <div class="summary-item">
                        <span class="label">Total Present:</span>
                        <span class="value"><?= $reports_data['total_present'] ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Total Absent:</span>
                        <span class="value"><?= $reports_data['total_absent'] ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Total Records:</span>
                        <span class="value"><?= $reports_data['total_present'] + $reports_data['total_absent'] ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Overall Rate:</span>
                        <span class="value"><?= $attendance_rate ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="export-options">
            <h3>Export Reports</h3>
            <div class="quick-actions">
                <a href="export_csv.php?type=students" class="btn">Export Students CSV</a>
                <a href="export_csv.php?type=attendance" class="btn">Export Attendance CSV</a>
                <a href="export_pdf.php" class="btn">Generate PDF Report</a>
            </div>
        </div>
    </section>
</main>

<style>
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.report-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.summary-stats {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}

.summary-item .label {
    font-weight: 600;
    color: #001125;
}

.summary-item .value {
    font-weight: bold;
    color: #001125;
}

.export-options {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #e2e8f0;
}

.quick-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 15px;
}

@media print {
    .btn, .page-header .btn {
        display: none;
    }
}
</style>
</body>
</html>