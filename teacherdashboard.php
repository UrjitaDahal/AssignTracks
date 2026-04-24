<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: Firstpage.php");
    exit();
}
require_once '../ajaxscripts/databaseconnection.php';
$user_id = $_SESSION['teacher_id'];
$query = "SELECT teacher_id, first_name,last_name,dob,email FROM teachers WHERE teacher_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
} else {
    $teacher = [
        'name' => 'N/A',
        'email' => 'N/A',

    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../CSS/teacherboard.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../CSS/teachermenu.css?v=<?php echo time(); ?>" rel="stylesheet">

</head>

<body>
    <?php include('teachermenu.php'); ?>
    <div class="container-fluid mt-4">
        <div class="row g-4">
            <!-- Announcements -->
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js">
        function loadAnnouncements() {
            $.get("../ajaxscripts/fetchannouncement.php", function(data) {
                $('#announcement-list').html(data);
            });
        }
        loadAnnouncements(); // Load initially
        setInterval(loadAnnouncements, 20000); // Refresh every 20 seconds
    </script>
</body>

</html>