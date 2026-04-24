<?
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: Firstpage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../CSS/adminmenu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/admindashboard.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include('adminmenu.php'); ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header text-white text-center">
                        <h5 class="mb-0">Teachers</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="teachers-list">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header text-white text-center">
                        <h5 class="mb-0">Students</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead class="table-success">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="students-list">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load teachers and students when the page is ready
            loadTeachers();
            loadStudents();
        });

        // Fetch and display teachers
        function loadTeachers() {
            $.get("../ajaxscripts/fetchteachers.php", function(data) {
                $('#teachers-list').html(data);
            });
        }

        // Fetch and display students
        function loadStudents() {
            $.get("../ajaxscripts/fetchstudents.php", function(data) {
                $('#students-list').html(data);
            });
        }

        // Delete a teacher
        function deleteTeacher(id) {
            if (confirm("Are you sure you want to delete this teacher?")) {
                $.post('../ajaxscripts/deleteteacher.php', {
                    teacher_id: id
                }, function(response) {
                    alert(response);
                    loadTeachers(); // Reload the teacher list
                });
            }
        }

        // Delete a student
        function deleteStudent(id) {
            if (confirm("Are you sure you want to delete this student?")) {
                $.post('../ajaxscripts/deletestudent.php', {
                    student_id: id
                }, function(response) {
                    alert(response);
                    loadStudents(); // Reload the student list
                });
            }
        }
    </script>
</body>
</html>