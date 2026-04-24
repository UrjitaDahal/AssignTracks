<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: Firstpage.php");
    exit();
}
require_once '../ajaxscripts/databaseconnection.php';
$student_id = $_SESSION['student_id'];
$query = "
    SELECT 
        a.assignment_id, 
        a.subject, 
        a.title, 
        sa.file_path, 
        sa.feedback, 
        sa.status 
    FROM student_assignments sa
    INNER JOIN assignmentsteacher a ON sa.assignment_id = a.assignment_id
    WHERE sa.student_id = ? AND sa.status = 'Graded'
    ORDER BY a.deadline ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <link rel="stylesheet" href="../CSS/studentfeedback.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/studentmenu.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include('studentmenu.php'); ?>
<br>
    <div class="dashboard-container">
        <h2>Teacher Feedback</h2>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Title</th>
                    <th>Submission</th>
                    <th>Feedback</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td><a href='" . htmlspecialchars($row['file_path']) . "' target='_blank'>View Submission</a></td>";
                        echo "<td>" . htmlspecialchars($row['feedback']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No graded feedback available yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
$conn->close();
?>