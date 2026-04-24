<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: Firstpage.php");
    exit();
}
require_once '../ajaxscripts/databaseconnection.php';

$teacher_id = $_SESSION['teacher_id'];
$error_message = $success_message = "";
$title = $description = $subject = "";
$max_file_size = 20 * 1024 * 1024; // 20MB
$allowed_types = ['txt', 'docx', 'pdf', 'pptx'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $deadline = $_POST['deadline'];
    $subject = htmlspecialchars(trim($_POST['subject']));

    date_default_timezone_set("Asia/Kathmandu");
    $current_time = time();
    $deadline_time = strtotime($deadline);
    $min_deadline_time = $current_time + (12 * 60 * 60);

    if ($deadline_time < $min_deadline_time) {
        $error_message = "❌ Deadline must be at least 12 hours from now.";
    } else {
        $file_path = "";

        if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
            $file_name = basename($_FILES['assignment_file']['name']);
            $file_size = $_FILES['assignment_file']['size'];
            $file_tmp = $_FILES['assignment_file']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_path = "uploads/" . time() . "_" . $file_name;

            // Validate file type
            if (!in_array($file_ext, $allowed_types)) {
                $error_message = "❌ Invalid file type. Only .txt, .docx, .pdf, .pptx are allowed.";
            }
            // Validate file size
            elseif ($file_size > $max_file_size) {
                $error_message = "❌ File size must be 20MB or less.";
            } else {
                if (!is_dir("uploads")) {
                    mkdir("uploads", 0777, true);
                }

                if (move_uploaded_file($file_tmp, $file_path)) {
                    // Insert into database
                    $insert_query = $conn->prepare("INSERT INTO assignmentsteacher (teacher_id, subject, title, description, deadline, file_path) VALUES (?, ?, ?, ?, ?, ?)");
                    $insert_query->bind_param("isssss", $teacher_id, $subject, $title, $description, $deadline, $file_path);

                    if ($insert_query->execute()) {
                        $success_message = "✅ Assignment created successfully!";
                        $title = $description = $subject = "";
                    } else {
                        $error_message = "❌ Error creating assignment. Please try again.";
                    }
                } else {
                    $error_message = "❌ Failed to upload file.";
                }
            }
        } else {
            $error_message = "❌ Please upload a valid file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment</title>
    <link rel="stylesheet" href="../CSS/teachermenu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/teachercreate.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include('teachermenu.php'); ?>

    <div class="form-container">
        <h2>📚 Create Assignment</h2>
        <p id="errorIcon"></p>
        <p id="deadlineError"></p>

        <?php if ($success_message): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>

            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>

            <label for="deadline">🗓️ Deadline for Submission:</label>
            <input type="datetime-local" id="deadline" name="deadline" required>

            <label for="assignment_file">Upload File (Max: 20MB, .txt, .docx, .pdf, .pptx):</label>
            <input type="file" name="assignment_file" id="assignment_file" required onchange="validateFile()">
            <p id="fileError" style="color: red;"></p>

            <button type="submit" class="submitBtn">📤 Create Assignment</button>
        </form>

        <script>
            function validateFile() {
                const fileInput = document.getElementById('assignment_file');
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
            document.addEventListener("DOMContentLoaded", function() {
                const deadlineInput = document.getElementById("deadline");
                const submitButton = document.getElementById("submitBtn");
                const errorMessage = document.getElementById("deadlineError");
                const errorIcon = document.getElementById("errorIcon");

                function validateDeadline() {
                    if (!deadlineInput.value) {
                        errorMessage.innerHTML = "⚠️ Please select a deadline.";
                        submitButton.disabled = true;
                        return;
                    }
                    const currentTime = new Date();
                    const deadlineTime = new Date(deadlineInput.value);
                    const minDeadlineTime = new Date(currentTime.getTime() + (12 * 60 * 60 * 1000));

                    if (deadlineTime < minDeadlineTime) {
                        errorMessage.innerHTML = "⏳ Deadline must be at least <b>12 hours</b> from now!";
                        errorMessage.classList.add("error-box");
                        errorIcon.innerHTML = "⚠️";
                        submitButton.disabled = true;
                    } else {
                        errorMessage.innerHTML = "✅ Deadline is valid!";
                        errorMessage.classList.remove("error-box");
                        errorIcon.innerHTML = "✔️";
                        submitButton.disabled = false;
                    }
                }

                deadlineInput.addEventListener("input", validateDeadline);
            });
        </script>

    </div>
</body>

</html>

<?php
$conn->close();
?>