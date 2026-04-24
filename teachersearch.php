<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: Firstpage.php");
    exit();
}

require_once '../ajaxscripts/databaseconnection.php'; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <link href="../CSS/teachermenu.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../CSS/stdlist.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>

<body>
    <?php include('teachermenu.php'); ?>
    <h2>Student List</h2>
    <input type="text" id="search" placeholder="Search by name...">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Enrollment Year</th>
            </tr>
        </thead>
        <tbody id="studentTable">
            <?php
            $query = "SELECT student_id, fname, lname, email, enrollyear FROM students";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                echo "<tr onclick=\"location.href='stddetailteach.php?id={$row['student_id']}'\">";
                echo "<td>{$row['fname']} {$row['lname']}</td>";
                echo "<td>{$row['email']}</td>";
                echo "<td>{$row['enrollyear']}</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <script>
        document.getElementById('search').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#studentTable tr');
            rows.forEach(row => {
                let name = row.cells[0].textContent.toLowerCase();
                row.style.display = name.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>

</html>