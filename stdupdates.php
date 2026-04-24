<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: Firstpage.php");
    exit();
}
require_once '../ajaxscripts/databaseconnection.php';

if (!isset($_GET['assignment_id'])) {
    echo "No assignment selected.";
    exit();
}

$assignment_id = $_GET['assignment_id'];
$student_id = $_SESSION['student_id'];

// Fetch assignment and student details
$query = "
    SELECT a.subject, a.title, sa.file_path, s.fname, s.lname
    FROM assignmentsteacher a
    LEFT JOIN student_assignments sa ON a.assignment_id = sa.assignment_id AND sa.student_id = ?
    LEFT JOIN students s ON sa.student_id = s.student_id
    WHERE a.assignment_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $student_id, $assignment_id);
$stmt->execute();
$result = $stmt->get_result();
$assignment = $result->fetch_assoc();
$stmt->close();

if (!$assignment) {
    echo "No record found for this assignment.";
    exit();
}

$file_error = "";
$max_file_size = 20 * 1024 * 1024; 
$allowed_types = ['txt', 'docx', 'pdf', 'pptx'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['new_file']) && $_FILES['new_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['new_file']['tmp_name'];
        $file_name = $_FILES['new_file']['name'];
        $file_size = $_FILES['new_file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($file_ext, $allowed_types)) {
            $file_error = "❌ Invalid file type. Only .txt, .docx, .pdf, .pptx are allowed.";
        }
        // Validate file size
        elseif ($file_size > $max_file_size) {
            $file_error = "❌ File size must be 50MB or less.";
        } else {
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_path = $upload_dir . uniqid() . "_" . $file_name;

            if (move_uploaded_file($file_tmp, $file_path)) {
                $update_query = "
                    UPDATE student_assignments
                    SET file_path = ?, submission_date = NOW()
                    WHERE student_id = ? AND assignment_id = ?
                ";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param('sii', $file_path, $student_id, $assignment_id);

                if ($update_stmt->execute()) {
                    echo "<script>alert('✅ File updated successfully!'); window.location.href='studentassignmentlist.php';</script>";
                    exit();
                } else {
                    $file_error = "❌ Error updating file.";
                }
            } else {
                $file_error = "❌ Failed to upload file.";
            }
        }
    } else {
        $file_error = "❌ Please select a valid file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Submission</title>
    <link rel="stylesheet" href="../CSS/studentmenu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/stdupdate.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include('studentmenu.php'); ?>

    <div class="container">
        <h2>📤 Update Submission</h2>
        <p><strong>Student Name:</strong> <?php echo htmlspecialchars($assignment['fname'] ?? '') . ' ' . htmlspecialchars($assignment['lname'] ?? ''); ?></p>
        <p><strong>Subject:</strong> <?php echo htmlspecialchars($assignment['subject']); ?></p>
        <p><strong>Title:</strong> <?php echo htmlspecialchars($assignment['title']); ?></p>
        <?php if ($assignment['file_path']): ?>
            <p><strong>Current Submission:</strong> <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" target="_blank">Download</a></p>
        <?php endif; ?>

        <?php if ($file_error): ?>
            <p class="error"><?php echo $file_error; ?></p>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="new_file">Upload New File (Max: 20MB, .txt, .docx, .pdf, .pptx):</label>
                <input type="file" name="new_file" id="new_file" required onchange="validateFile()">
                <p id="fileError" style="color: red;"></p>
            </div>
            <button type="submit">Update File</button>
        </form>
    </div>

    <script>
        function validateFile() {
            const fileInput = document.getElementById('new_file');
            const fileError = document.getElementById('fileError');
            const maxFileSize = 20 * 1024 * 1024;
            const allowedExtensions = ['txt', 'docx', 'pdf', 'pptx'];

            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const fileSize = file.size;
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExt)) {
                    fileError.textContent = "❌ Invalid file type. Only .txt, .docx, .pdf, .pptx are allowed.";
                    fileInput.value = "";
                    return;
                }
                if (fileSize > maxFileSize) {
                    fileError.textContent = "❌ File size must be 20MB or less.";
                    fileInput.value = "";
                    return;
                }

                fileError.textContent = "";
            }
        }
    </script>

</body>

</html>

<?php $conn->close(); ?>