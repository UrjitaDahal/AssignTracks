<?php
session_start();


if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === "admin") {
    unset($_SESSION['admin_id']);
    unset($_SESSION['user_type']);

}

header("Location: Firstpage.php");
exit();
