<?php
require_once '../ajaxscripts/databaseconnection.php'; // Database connection file

// Check if student ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid student ID.";
    exit();
}

$student_id = intval($_GET['id']); // Get student ID from URL

// Fetch student details
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();
$stmt->close();

if (!$student) {
    echo "Student not found.";
    exit();
}

// Fetch student submissions
$stmt = $conn->prepare("SELECT * FROM student_assignments WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$submissions_result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details</title>
    <link href="../CSS/teachermenu.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../CSS/stddetailteach.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>

<body>
    <?php include('teachermenu.php'); ?>
    <h2>Student Details</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($student['fname'] . ' ' . $student['lname']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
    <p><strong>Gender:</strong> <?php echo htmlspecialchars($student['gender']); ?></p>
    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($student['dob']); ?></p>
    <p><strong>Year of Enrollment:</strong> <?php echo htmlspecialchars($student['enrollyear']); ?></p>

    <h3>Submission Records</h3>
    <table border="1">
        <tr>
            <th>Title</th>
            <th>Date Submitted</th>
            <th>File</th>
        </tr>
        <?php while ($submission = $submissions_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($submission['description']); ?></td>
                <td><?php echo htmlspecialchars($submission['submission_date']); ?></td>
                <td><a href="../uploads/<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank">View File</a></td>
            </tr>
        <?php } ?>
    </table>
    <br>
    <a href="teachersearch.php">Back to Student List</a>
</body>

</html>