<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: Firstpage.php");
    exit();
}
require_once '../ajaxscripts/databaseconnection.php';

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $message = trim($_POST["message"]);
    $teacher_id = $_SESSION['teacher_id']; 

    if (empty($title) || empty($message)) {
        $error_message = "⚠️ Title and message cannot be empty!";
    } else {
        $query = "INSERT INTO announcements (teacher_id, title, message, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            $error_message = "❌ Error in SQL Query: " . $conn->error;
        } else {
            $stmt->bind_param("iss", $teacher_id, $title, $message);
            if ($stmt->execute()) {
                $success_message = "✅ Announcement posted successfully!";
            } else {
                $error_message = "❌ Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Announcement</title>
    <link rel="stylesheet" href="../CSS/teacherannounce.css">
    <link rel="stylesheet" href="../CSS/teachermenu.css">
</head>

<body>
    <?php include('teachermenu.php'); ?>

    <div class="container">
        <div class="announcement-box">
            <div class="announcement-header">📢 Post Announcement</div>

            <!-- Success Message -->
            <?php if (!empty($success_message)) : ?>
                <p class="success-message"><?php echo $success_message; ?></p>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (!empty($error_message)) : ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form action="" method="POST">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>

                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="4" required></textarea>

                <button type="submit" class="btn-primary">📤 Post Announcement</button>
            </form>
        </div>
    </div>
</body>

</html>