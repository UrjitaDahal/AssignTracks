<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Firstpage.php"); 
    exit();
}
require_once '../ajaxscripts/functions.php';
require_once '../ajaxscripts/databaseconnection.php';
$first_name = $last_name = $email = $username = $password = $dob = $gender = "";
$first_name_error = $last_name_error = $email_error = $username_error = $password_error = $dob_error = $gendererr = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate First Name
    if (empty($_POST["first_name"])) {
        $first_name_error = "First name is required.";
    } else {
        $first_name = test_input($_POST["first_name"]);
    }

    // Validate Last Name
    if (empty($_POST["last_name"])) {
        $last_name_error = "Last name is required.";
    } else {
        $last_name = test_input($_POST["last_name"]);
    }

    // Validate Email
    if (empty($_POST["email"])) {
        $email_error = "Email is required.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format.";
    } else {
        $email = test_input($_POST["email"]);

        // Check if email already exists in the database
        $query = "SELECT email FROM teachers WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $email_error = "Email already exists.";
        }
        $stmt->close();
    }

    // Validate Username
    if (empty($_POST["username"])) {
        $username_error = "Username is required.";
    } else {
        $username = test_input($_POST["username"]);

        // Check if username already exists in the database
        $query = "SELECT username FROM teachers WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $username_error = "Username already exists.";
        }
        $stmt->close();
    }

    // Validate Password
    if (empty($_POST["password"])) {
        $password_error = "Password is required.";
    } elseif (strlen($_POST["password"]) < 8) {
        $password_error = "Password must be at least 8 characters long.";
    } else {
        $password = test_input($_POST["password"]);
    }

    // Validate DOB
    if (empty($_POST["dob"])) {
        $dob_error = "Date of Birth is required.";
    } else {
        $dob = test_input($_POST["dob"]);
    }
    if (empty($_POST["gender"])) {
        $gendererr = "Gender is required";
    } else {
        $gender = test_input($_POST["gender"]);
    }
    // If no errors, proceed to insert data into the database
    if (empty($first_name_error) && empty($last_name_error) && empty($email_error) && empty($username_error) && empty($password_error) && empty($dob_error)) {
        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL statement to insert data into the database
        $query = "INSERT INTO teachers (first_name, last_name,dob,gender, email, username, password) VALUES (?,?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $first_name, $last_name, $dob, $email, $username, $hashedPassword);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            // Redirect to the dashboard upon successful registration
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Registration</title>
    <link rel="stylesheet" href="../CSS/admincreateteacher.css?v<?php echo time() ?>">
    <link rel="stylesheet" href="../CSS/adminmenu.css?v<?php echo time() ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('adminmenu.php'); ?>
    <br>
    <h2>Register Teacher here </h2>
    <div class="form-container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
            <span class="error"><?php echo $first_name_error ?? ''; ?></span>
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>
            <span class="error"><?php echo $last_name_error ?? ''; ?></span>
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required>
            <span class="error"><?php echo $dob_error ?? ''; ?></span>
            <label for="gender">Gender:</label>
            <div>
                <input type="radio" id="male" name="gender" value="male" <?php if ($gender == 'male') echo 'checked'; ?>>
                <label for="male">Male</label>
                <input type="radio" id="female" name="gender" value="female" <?php if ($gender == 'female') echo 'checked'; ?>>
                <label for="female">Female</label>
                <input type="radio" id="other" name="gender" value="other" <?php if ($gender == 'other') echo 'checked'; ?>>
                <label for="other">Other</label>
            </div>
            <span class="error"><?php echo $gendererr; ?></span><br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <span class="error"><?php echo $email_error ?? ''; ?></span>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <span class="error"><?php echo $username_error ?? ''; ?></span>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <span class="error"><?php echo $password_error ?? ''; ?></span>
            <button type="submit">Register</button>
            <a href="loginteacher.php">Registered? Login Here</a>
        </form>
    </div>
</body>
</html>