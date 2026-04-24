<?php
require_once '../ajaxscripts/databaseconnection.php'; 
session_start();
$admin_id="";
$_SESSION['user_type'] = "admin";
$_SESSION['admin_id'] = $admin_id;
session_write_close();
$username_error = $password_error = "";
$username = $password = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["username"])) {
        $username_error = "Username is required.";
    } else {
        $username = $_POST["username"];
    }
    if (empty($_POST["password"])) {
        $password_error = "Password is required.";
    } elseif (strlen($_POST["password"]) < 8) {
        $password_error = "Password must be at least 8 characters long.";
    } else {
        $password = $_POST["password"];
    }
    if (empty($username_error) && empty($password_error)) {
        $stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) { 
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();
            if (password_verify($password, $hashedPassword)) {
                header("Location: admindashboard.php");
                exit();
            } else {
                $password_error = "Invalid password.";
            }
        } else {
            $username_error = "No account found with that username.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link  rel="stylesheet" href="../CSS/loginstudent.css?v<?php echo time()?>">
</head>
<body>
    <div class="form-contain">
        <div class="form-header">
        <img src="../images/todoimg.jpg" alt="LOGO" width=60 height=60> 
        <h2 style="text-decoration:underline;">AssignTracks</h2>
            <h2>Admin Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
            <span class="error"><?php echo $username_error; ?></span>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
            <span class="error"><?php echo $password_error; ?></span>
            <br>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
