<?php
session_start();
require_once '../ajaxscripts/databaseconnection.php';

$username_error = $password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username)) {
        $username_error = "Username is required";
    }
    if (empty($password)) {
        $password_error = "Password is required";
    }

    if (empty($username_error) && empty($password_error)) {
        $query = "SELECT teacher_id, username, password FROM teachers WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $user_username, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // ✅ Store teacher ID correctly
                $_SESSION['teacher_id'] = $user_id;
                $_SESSION['username'] = $user_username;
                $_SESSION['role'] = 'teacher';

                header("Location: teacherdashboard.php");
                exit();
            } else {
                $password_error = "Invalid username or password.";
            }
        } else {
            $username_error = "No account found with that username.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    <link rel="stylesheet" href="../CSS/loginstudent.css?v<?php echo time() ?>">
</head>

<body>
    <div class="form-contain">
        <img src="../images/todoimg.jpg" alt="LOGO" width="60" height="60">
        <h2 style="text-decoration: underline;">AssignTracks</h2>
        <h2>Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <!-- Username input -->
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
            <span class="error" style="color: red;"><?php echo $username_error; ?></span><br>

            <!-- Password input -->
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" value="">
            <span class="error" style="color: red;"><?php echo $password_error; ?></span><br>

            <!-- Submit button -->
            <button type="submit">Login</button>
            <p style="text-decoration: underline solid blue;">Not registered? Consult with the admin.</p>
        </form>
    </div>
</body>

</html>