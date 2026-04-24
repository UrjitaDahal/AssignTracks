<?php
session_start();
 if (!isset($_SESSION['student_id'])) {
        header("Location: Firstpage.php"); 
        exit();
 }
require_once '../ajaxscripts/databaseconnection.php';
$student_id = $_SESSION['student_id'];
$query = "SELECT fname, lname, email, dob FROM students WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/studentmenu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/studentdashboard.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include('studentmenu.php') ?>

    <div class="container-fluid mt-4">
        <div class="row g-4">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow bg-light">
                    <div class="card-header">📢 Announcements</div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                            <thead class="table-primary">
                                <tr>
                                  
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="announcement-list">
                                <tr>
                                    <td colspan="3" class="text-center">No announcements available.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function loadAnnouncements() {
                $.get("../ajaxscripts/fetchannouncement.php", function(data) {
                    $('#announcement-list').html(data);
                });
            }
            loadAnnouncements();
            setInterval(loadAnnouncements, 20000);
        });
    </script>

</body>

</html>

