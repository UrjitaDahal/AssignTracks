<?php
    require_once '../ajaxscripts/functions.php';
    require_once '../ajaxscripts/databaseconnection.php';

    $fname = $lname = $dob = $gender = $email = $username = $password = $year = "";
    $fnameerr = $lnameerr = $doberr = $gendererr = $emailerr = $usernameerr = $passworderr = $yearerr = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate First Name
        if (empty($_POST["fname"])) {
            $fnameerr = "First name is required";
        } else {
            $fname = test_input($_POST["fname"]);
        }

        // Validate Last Name
        if (empty($_POST["lname"])) {
            $lnameerr = "Last name is required";
        } else {
            $lname = test_input($_POST["lname"]);
        }

        // Validate Date of Birth
        if (empty($_POST["dob"])) {
            $doberr = "Date of birth is required";
        } else {
            $dob = test_input($_POST["dob"]);
            if (!validate_dob($dob)) {
                $doberr = "Please select a correct date of birth.";
            }
        }

        // Validate Gender
        if (empty($_POST["gender"])) {
            $gendererr = "Gender is required";
        } else {
            $gender = test_input($_POST["gender"]);
        }

        // Validate Email
        if (empty($_POST["email"])) {
            $emailerr = "Email is required";
        } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $emailerr = "Invalid email format";
        } else {
            $email = test_input($_POST["email"]);

            // Check if email already exists
            $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $emailerr = "Email already exists.";
            }
            $stmt->close();
        }

        // Validate Username
        if (empty($_POST["username"])) {
            $usernameerr = "Username is required";
        } else {
            $username = test_input($_POST["username"]);
        }

        // Validate Password
        if (empty($_POST["password"])) {
            $passworderr = "Password is required";
        } elseif (strlen($_POST["password"]) < 8) {
            $passworderr = "Password must be at least 8 characters long.";
        } else {
            $password = test_input($_POST["password"]);
        }

        // Validate Year of Enrollment
        if (empty($_POST["enrollyear"])) {
            $yearerr = "Year of enrollment is required";
        } else {
            $year = test_input($_POST["enrollyear"]);
            if (!validate_year($year)) {
                $yearerr = "Year of enrollment cannot be in the future.";
            }
        }

        // Proceed if no validation errors
        if (empty($fnameerr) && empty($lnameerr) && empty($doberr) && empty($gendererr) &&
            empty($emailerr) && empty($usernameerr) && empty($passworderr) && empty($yearerr)) {

            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Prepare an SQL statement to insert user data into the database
            $stmt = $conn->prepare("INSERT INTO students (fname, lname, dob, gender, email, username, password, enrollyear) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssi", $fname, $lname, $dob, $gender, $email, $username, $hashedPassword, $year);

            // Execute the statement
            if ($stmt->execute()) {
                // Redirect to the dashboard if the data is successfully saved in the database
                header("Location: dashboard.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }

            // Close the statement and the database connection
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
    <title>Document</title>
    <link  rel="stylesheet" href="../CSS/registerstudent.css?v<?php echo time()?>">
</head>
<body>
    <div class="form-contain">
       <img src="../images/todoimg.jpg" alt="LOGO" width=60 height=60> 
    <h2 style="text-decoration:underline;">AssignTracks</h2>
        <h2>Register here</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

<label for="fname">First Name:</label>
<input type="text" id="fname" name="fname" value="<?php echo $fname; ?>">
<span class="error"><?php echo $fnameerr; ?></span><br>

<label for="lname">Last Name:</label>
<input type="text" id="lname" name="lname" value="<?php echo $lname; ?>">
<span class="error"><?php echo $lnameerr; ?></span><br>

<label for="dob">Date Of Birth:</label>
<input type="date" id="dob" name="dob" value="<?php echo $dob; ?>">
<span class="error"><?php echo $doberr; ?></span><br>

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
<input type="email" id="email" name="email" value="<?php echo $email; ?>">
<span class="error"><?php echo $emailerr; ?></span><br>

<label for="username">Username:</label>
<input type="text" id="username" name="username" value="<?php echo $username; ?>">
<span class="error"><?php echo $usernameerr; ?></span><br>

<label for="password">Password:</label>
<input type="password" id="password" name="password" value="">
<span class="error"><?php echo $passworderr; ?></span><br>

<label for="enrollyear">year of enrollment (Batch):</label>
<input type="number" id="enrollyear" name="enrollyear" value="<?php echo $year; ?>">
<span class="error"><?php echo $yearerr; ?></span><br>

<button type="submit">Register</button><br><br>

<a href="studentlogin.php" style="text-decoration: underline; color:black;"> Registered Student? Login Here</a>
</form>
</div>
</body>
</html>