<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: Firstpage.php");
    exit();
}
require_once '../ajaxscripts/databaseconnection.php';

$teacher_id = $_SESSION['teacher_id'];
$query = $conn->prepare("SELECT first_name, last_name, email, dob FROM teachers WHERE teacher_id = ?");
$query->bind_param("i", $teacher_id);
$query->execute();
$result = $query->get_result();
$teacher = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_first_name = htmlspecialchars($_POST['first_name']);
    $new_last_name = htmlspecialchars($_POST['last_name']);
    $new_email = htmlspecialchars($_POST['email']);
    $new_dob = $_POST['dob'];

    $today = date("Y-m-d");
    $minDate = date("Y-m-d", strtotime("-25 years"));

    if ($new_dob >= $today) {
        $error = "Date of birth cannot be today or in the future!";
    } elseif ($new_dob > $minDate) {
        $error = "You must be at least 25 years old!";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        $update_query = $conn->prepare("UPDATE teachers SET first_name = ?, last_name = ?, email = ?, dob = ? WHERE teacher_id = ?");
        $update_query->bind_param("ssssi", $new_first_name, $new_last_name, $new_email, $new_dob, $teacher_id);
        $update_query->execute();

        if ($update_query->affected_rows > 0) {
            $success = "Profile updated successfully!";
            header("Refresh:1");
        } else {
            $error = "No changes made!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../CSS/teachermenu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/teacherprofile.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include('teachermenu.php') ?>
    <div class="container">
        <div class="card border-0 shadow bg-light">
            <div class="card-header">📌 Teacher Profile</div>
            <div class="card-body text-center">
                <?php if (isset($success)) {
                    echo "<div class='alert alert-success'>$success</div>";
                } ?>
                <?php if (isset($error)) {
                    echo "<div class='alert alert-danger'>$error</div>";
                } ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label"><strong>First Name:</strong></label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($teacher['first_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Last Name:</strong></label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($teacher['last_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Email:</strong></label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Date of Birth:</strong></label>
                        <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($teacher['dob']); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dobField = document.querySelector('input[name="dob"]');
            const today = new Date();
            const minAge = 25;
            const minDate = new Date(today.getFullYear() - minAge, today.getMonth(), today.getDate());
            dobField.setAttribute("max", minDate.toISOString().split("T")[0]);
            document.querySelector("form").addEventListener("submit", function(event) {
                const selectedDob = new Date(dobField.value);
                if (!dobField.value) {
                    alert("Please select a valid Date of Birth.");
                    event.preventDefault();
                } else if (selectedDob >= today) {
                    alert("Date of birth cannot be today or in the future!");
                    event.preventDefault();
                } else if (selectedDob > minDate) {
                    alert("You must be at least 25 years old!");
                    event.preventDefault();
                }
            });
        });
    </script>
</body>

</html>