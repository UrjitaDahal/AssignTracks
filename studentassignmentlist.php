<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: Firstpage.php");
    exit();
}
$student_id = $_SESSION['student_id'];
require_once '../ajaxscripts/databaseconnection.php';
$query = "
    SELECT a.assignment_id, a.subject, a.title, a.description, a.deadline, af.file_path 
    FROM assignmentsteacher a
    LEFT JOIN assignmentsteacher af ON a.assignment_id = af.assignment_id
    ORDER BY a.deadline ASC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Homework List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/studentmenu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../CSS/studentdashboard.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include('studentmenu.php'); ?>
<br><br>
    <div class="container">
        <div class="card border-0 shadow rounded-4">
            <div class="card-header">📚 Homework Assignments</div>
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th>Subject</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Deadline</th>
                            <th>Files</th>
                            <th colspan="2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require_once '../ajaxscripts/databaseconnection.php';
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        $query = "
                            SELECT a.assignment_id, a.subject, a.title, a.description, a.deadline, af.file_path 
                            FROM assignmentsteacher a
                            LEFT JOIN assignmentsteacher af ON a.assignment_id = af.assignment_id
                            ORDER BY a.deadline ASC
                        ";
                        $result = $conn->query($query);
                        $assignments = [];
                        date_default_timezone_set('Asia/Kathmandu');
                        while ($row = $result->fetch_assoc()) {
                            $assignment_id = $row['assignment_id'];
                            if (!isset($assignments[$assignment_id])) {
                                $assignments[$assignment_id] = [
                                    'subject' => $row['subject'],
                                    'title' => $row['title'],
                                    'description' => $row['description'],
                                    'deadline' => $row['deadline'],
                                    'files' => []
                                ];
                            }
                            if ($row['file_path']) {
                                $assignments[$assignment_id]['files'][] = $row['file_path'];
                            }
                        }
                        foreach ($assignments as $assignment_id => $assignment) {
                            $deadline = new DateTime($assignment['deadline']);
                            $current_time = new DateTime();
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($assignment['subject']) . "</td>";
                            echo "<td>" . htmlspecialchars($assignment['title']) . "</td>";
                            echo "<td>" . htmlspecialchars($assignment['description']) . "</td>";
                            echo "<td>" . htmlspecialchars($assignment['deadline']) . "</td>";
                            echo "<td class='file-links'>";
                            foreach ($assignment['files'] as $file) {
                                echo "<a href='$file' target='_blank'>Download</a>";
                            }
                            echo "</td>";
                            if ($current_time > $deadline) {
                                echo "<td colspan='2'><span class='deadline-crossed'>Deadline crossed</span></td>";
                            } else {
                                echo "<td><a href='stdsubmit.php?assignment_id=$assignment_id' class='btn btn-primary'>Submit</a></td>";
                                echo "<td><a href='stdupdates.php?assignment_id=$assignment_id' class='btn btn-warning'>Update</a></td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>