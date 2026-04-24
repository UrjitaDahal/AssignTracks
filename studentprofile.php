<?php
session_start();
 if (!isset($_SESSION['student_id'])) {
        header("Location: Firstpage.php"); 
        exit();
 }
require_once '../ajaxscripts/databaseconnection.php'; 


$student_id = $_SESSION['student_id']; 
$query = $conn->prepare("SELECT fname, lname, email, dob FROM students WHERE student_id = ?");
$query->bind_param("i", $student_id);
$query->execute();
$result = $query->get_result();
$student = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_fname = htmlspecialchars($_POST['fname']);
    $new_lname = htmlspecialchars($_POST['lname']);
    $new_email = htmlspecialchars($_POST['email']);
    $new_dob = $_POST['dob'];

 
    $today = date("Y-m-d");
    $minDate = date("Y-m-d", strtotime("-15 years")); 

    if ($new_dob >= $today) {
        $error = "Date of birth cannot be today or in the future!";
    } elseif ($new_dob > $minDate) {
        $error = "You must be at least 15 years old!";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
  
        $update_query = $conn->prepare("UPDATE students SET fname = ?, lname = ?, email = ?, dob = ? WHERE student_id = ?");
        $update_query->bind_param("ssssi", $new_fname, $new_lname, $new_email, $new_dob, $student_id);
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
    <title>Student Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../CSS/studentmenu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/studentprofile.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include('studentmenu.php') ?>
    <div class="container">
        <div class="card border-0 shadow bg-light">
            <div class="card-header">📌 Student Profile</div>
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
                        <input type="text" name="fname" class="form-control" value="<?php echo htmlspecialchars($student['fname']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Last Name:</strong></label>
                        <input type="text" name="lname" class="form-control" value="<?php echo htmlspecialchars($student['lname']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Email:</strong></label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Date of Birth:</strong></label>
                        <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($student['dob']); ?>" required>
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
            const minAge = 14;
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
                    alert("You must be at least 14 years old!");
                    event.preventDefault();
                }
            });
        });
    </script>

</body>

</html>