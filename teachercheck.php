<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: Firstpage.php");
    exit();
}
require_once '../ajaxscripts/databaseconnection.php';
$teacher_id = $_SESSION['teacher_id'];
$query = "
   SELECT 
    a.assignment_id, 
    a.subject, 
    a.title, 
    a.description, 
    a.deadline, 
    s.fname, 
    s.lname, 
    ss.file_path 
FROM 
    assignmentsteacher a
LEFT JOIN 
    student_assignments ss ON a.assignment_id = ss.assignment_id
LEFT JOIN 
    students s ON ss.student_id = s.student_id
WHERE 
    a.teacher_id = ?
ORDER BY 
    a.deadline ASC, a.assignment_id ASC;

";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$assignments = [];
while ($row = $result->fetch_assoc()) {
    $assignment_id = $row['assignment_id'];
    if (!isset($assignments[$assignment_id])) {
        $assignments[$assignment_id] = [
            'subject' => $row['subject'],
            'title' => $row['title'],
            'description' => $row['description'],
            'deadline' => $row['deadline'],
            'submissions' => []
        ];
    }
    if ($row['fname']) {
        $assignments[$assignment_id]['submissions'][] = [
            'fname' => $row['fname'],
            'lname' => $row['lname'],
            'file_path' => $row['file_path']
        ];
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher View - Student Assignments</title>
    <link rel="stylesheet" href="../CSS/teachercheck.css?v<?php echo time() ?>">
    <link rel="stylesheet" href="../CSS/teachermenu.css?v<?php echo time() ?>">

</head>

<body>
    <?php include('teachermenu.php') ?>
    <div class="container">
        <h2> ✔️Student Submissions</h2>

        <?php foreach ($assignments as $assignment_id => $assignment) : ?>
            <div class="assignment">
                <h3><?php echo htmlspecialchars($assignment['title']); ?> (<?php echo htmlspecialchars($assignment['subject']); ?>)</h3>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($assignment['description']); ?></p>
                <p><strong>Deadline:</strong> <?php echo htmlspecialchars($assignment['deadline']); ?></p>

                <div class="submissions">
                    <h4>Student Submissions:</h4>
                    <?php if (!empty($assignment['submissions'])) : ?>
                        <?php foreach ($assignment['submissions'] as $submission) : ?>
                            <div class="submission-entry">
                                <span><?php echo htmlspecialchars($submission['fname'] ?? '') . ' ' . htmlspecialchars($submission['lname'] ?? ''); ?></span>
                                <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank" class="button">Download Submission</a>
                                <a href="teacherfeedback.php" class="button">Give Feedback</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>No submissions yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>

<?php
$conn->close();
?>