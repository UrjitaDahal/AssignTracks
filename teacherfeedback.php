<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: Firstpage.php");
    exit();
}
require_once '../ajaxscripts/databaseconnection.php'; 
$teacher_id = $_SESSION['teacher_id'];
$feedback_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_feedback'])) {
    $assignment_id = $_POST['assignment_id'];
    $student_id = $_POST['student_id'];
    $feedback = trim($_POST['feedback']);
    if (empty($feedback)) {
        $feedback_error = "Feedback cannot be empty.";
    } else {
        $stmt = $conn->prepare("
            UPDATE student_assignments 
            SET feedback = ?, status = 'Graded' 
            WHERE assignment_id = ? AND student_id = ?
        ");
        $stmt->bind_param("sii", $feedback, $assignment_id, $student_id);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Feedback submitted successfully.</p>";
        } else {
            echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}
$query = "
    SELECT a.assignment_id, a.title, s.student_id, s.fname, s.lname, sa.file_path, sa.status, sa.feedback
    FROM assignmentsteacher a
    INNER JOIN student_assignments sa ON a.assignment_id = sa.assignment_id
    INNER JOIN students s ON sa.student_id = s.student_id
    WHERE a.teacher_id = ?
    ORDER BY sa.status ASC, a.title ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provide Feedback</title>
    <link  rel="stylesheet" href="../CSS/teachermenu.css?v<?php echo time()?>">
    <link  rel="stylesheet" href="../CSS/teacherfeedback.css?v<?php echo time()?>">
</head>
<body>
<?php include('teachermenu.php') ?>
    <div class="container">
        <h2>Provide Feedback</h2>
        <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Assignment Title</th>
                    <th>Student Name</th>
                    <th>Status</th>
                    <th>File</th>
                    <th>Feedback</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['fname'] . " " . $row['lname']); ?></td>
                    <td class="<?php echo $row['status'] === 'Graded' ? 'status-graded' : 'status-pending'; ?>">
                        <?php echo htmlspecialchars($row['status']); ?>
                    </td>
                    <td>
                        <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">View File</a>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'Pending'): ?>
                        <form action="" method="POST">
                            <textarea name="feedback" placeholder="Enter feedback here"></textarea>
                            <input type="hidden" name="assignment_id" value="<?php echo $row['assignment_id']; ?>">
                            <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                            <button type="submit" name="submit_feedback">Submit Feedback</button>
                        </form>
                        <?php else: ?>
                        <?php echo htmlspecialchars($row['feedback']); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No submissions available to grade.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
