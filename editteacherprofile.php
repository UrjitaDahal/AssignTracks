<?php
session_start();
require_once '../ajaxscripts/databaseconnection.php';

// Check if the teacher is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginteacher.php");
    exit();
}

// Fetch current teacher data
$teacher_id = $_SESSION['user_id'];
$query = "SELECT first_name, last_name, dob, email FROM teachers WHERE teacher_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $dob = $_POST["dob"]; // Capture the updated DOB

    $update_query = "UPDATE teachers SET first_name = ?, last_name = ?, email = ?, dob = ? WHERE teacher_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssssi", $first_name, $last_name, $email, $dob, $teacher_id);

    if ($update_stmt->execute()) {
        $success_message = "Profile updated successfully!";
    } else {
        $error_message = "Error updating profile: " . $update_stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher Profile</title>
    <link href="../CSS/bootstrap.min.css" rel="stylesheet">
    <link href="../CSS/teacheredit.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/teachermenu.css?v<?php echo time(); ?>">
</head>

<body>
    <?php include('teachermenu.php'); ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card rounded-lg shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Edit Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)) : ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if (isset($error_message)) : ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    value="<?php echo htmlspecialchars($teacher['first_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                    value="<?php echo htmlspecialchars($teacher['last_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob"
                                    value="<?php echo htmlspecialchars($teacher['dob']); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                            <a href="teacherdashboard.php" class="btn btn-secondary w-100 mt-2">Back to Dashboard</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../JS/bootstrap.bundle.min.js"></script>
</body>

</html>