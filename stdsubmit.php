<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: Firstpage.php");
    exit();
}
require_once '../ajaxscripts/databaseconnection.php';

$student_id = $_SESSION['student_id'];
$student_query = $conn->prepare("SELECT fname, lname, email FROM students WHERE student_id = ?");
$student_query->bind_param("i", $student_id);
$student_query->execute();
$student_result = $student_query->get_result();
$student = $student_result->fetch_assoc();

$homework_query = $conn->query("SELECT assignment_id, subject, title FROM assignmentsteacher ORDER BY deadline ASC");

$file_error = $success_message = "";
$description = "";
$max_file_size = 20 * 1024 * 1024; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignment_id = $_POST['assignment_id'];
    $description = htmlspecialchars(trim($_POST['description']));

    // Allowed file types
    $allowed_types = ['txt', 'docx', 'pdf', 'pptx'];

    if (isset($_FILES['homework_file']) && $_FILES['homework_file']['error'] == 0) {
        $file_name = basename($_FILES['homework_file']['name']);
        $file_size = $_FILES['homework_file']['size'];
        $file_tmp = $_FILES['homework_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_path = "uploads/" . time() . "_" . $file_name;

        // Validate file type
        if (!in_array($file_ext, $allowed_types)) {
            $file_error = "❌ Invalid file type. Only .txt, .docx, .pdf, .pptx are allowed.";
        }
        // Validate file size
        elseif ($file_size > $max_file_size) {
            $file_error = "❌ File size must be 20MB or less.";
        } else {
            // Create "uploads" directory if it doesn't exist
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }

            // Move uploaded file
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Insert into database with timestamp
                $insert_query = $conn->prepare("INSERT INTO student_assignments (assignment_id, student_id, description, file_path, submission_date) VALUES (?, ?, ?, ?, NOW())");
                $insert_query->bind_param("iiss", $assignment_id, $student_id, $description, $file_path);

                if ($insert_query->execute()) {
                    $success_message = "✅ Homework submitted successfully!";
                    $description = "";
                } else {
                    $file_error = "❌ Error submitting your homework. Please try again.";
                }
            } else {
                $file_error = "❌ Failed to upload file.";
            }
        }
    } else {
        $file_error = "❌ Please upload a valid file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Homework Submission</title>
    <link rel="stylesheet" href="../CSS/studentmenu.css?v=<?php echo time() ?>">
    <link rel="stylesheet" href="../CSS/stdsubmit.css?v=<?php echo time() ?>">
</head>

<body>
    <?php include('studentmenu.php') ?>
    <div class="container">
        <h2>📤 Submit Homework</h2>
        <?php if ($success_message): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if ($file_error): ?>
            <p class="error"><?php echo $file_error; ?></p>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="assignment_id">Select Assignment:</label>
            <select name="assignment_id" required>
                <?php while ($row = $homework_query->fetch_assoc()): ?>
                    <option value="<?= $row['assignment_id'] ?>"><?= htmlspecialchars($row['subject'] . " - " . $row['title']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="description">Description (optional):</label>
            <textarea name="description"><?= htmlspecialchars($description) ?></textarea>

            <label for="homework_file">Upload Homework (Max: 20MB, .txt, .docx, .pdf, .pptx):</label>
            <input type="file" name="homework_file" id="homework_file" required onchange="validateFile()">
            <p id="fileError" style="color: red;"></p>

            <button type="submit">Submit</button>
        </form>

        <script>
            function validateFile() {
                const fileInput = document.getElementById('homework_file');
                const fileError = document.getElementById('fileError');
                const maxFileSize = 20 * 1024 * 1024;
                const allowedExtensions = ['txt', 'docx', 'pdf', 'pptx'];

                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const fileSize = file.size;
                    const fileName = file.name;
                    const fileExt = fileName.split('.').pop().toLowerCase();

                    // Validate file type
                    if (!allowedExtensions.includes(fileExt)) {
                        fileError.textContent = "❌ Invalid file type. Only .txt, .docx, .pdf, .pptx are allowed.";
                        fileInput.value = "";
                        return;
                    }

                    // Validate file size
                    if (fileSize > maxFileSize) {
                        fileError.textContent = "❌ File size must be 20MB or less.";
                        fileInput.value = "";
                        return;
                    }

                    fileError.textContent = "";
                }
            }
        </script>

    </div>
</body>

</html>
<?php
$conn->close();
?>