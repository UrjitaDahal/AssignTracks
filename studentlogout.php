<?php
session_start();
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === "student") {
    unset($_SESSION['student_id']);
    unset($_SESSION['user_type']);
}
if (empty($_SESSION)) {
    session_destroy();
}
ob_start();
header("Location: Firstpage.php");
ob_end_flush();
exit();


