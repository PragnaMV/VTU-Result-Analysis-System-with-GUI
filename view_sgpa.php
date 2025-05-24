<?php
$servername = "localhost";
$username = "root";
$password = "";#your password
$dbname = "";#your dbname

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get parameters safely
$department = isset($_GET['department']) ? $_GET['department'] : '';
$scheme = isset($_GET['scheme']) ? $_GET['scheme'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$usn = isset($_GET['usn']) ? $_GET['usn'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View SGPA & CGPA</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header-main {
            font-size: 26px;
            font-weight: bold;
            color:#000000 ;
            text-align: center;
            margin-bottom: 10px;
        }
        .header-sub {
            font-size: 16px;
            text-align: center;
            color: #000000;
            margin-bottom: 20px;
        }
        .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #004488;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <?php if (empty($usn)) { ?>
            <div class="header-main">Department: <?php echo htmlspecialchars($department); ?></div>
            <div class="header-sub">Scheme: <?php echo htmlspecialchars($scheme); ?> | Semester: <?php echo htmlspecialchars($semester); ?></div>

            <h5 class="mt-4 text-center">Select a USN:</h5>
            <ul class="list-group">
                <?php
                $dir = "C:/wamp64/www/project/templates/uploads/$department/$scheme/$semester";
                if (is_dir($dir)) {
                    $files = scandir($dir);
                    $pdfs = array_filter($files, function ($file) {
                        return pathinfo($file, PATHINFO_EXTENSION) === 'pdf';
                    });

                    if (!empty($pdfs)) {
                        foreach ($pdfs as $pdf) {
                            $filename = pathinfo($pdf, PATHINFO_FILENAME);
                            $usn = explode('_', $filename)[0]; ?>
                            <li class="list-group-item">
                                <span><?php echo $usn; ?></span>
                                <div>
                                    <a href="uploads/<?php echo urlencode($department); ?>/<?php echo urlencode($scheme); ?>/<?php echo urlencode($semester); ?>/<?php echo urlencode($pdf); ?>" class="btn btn-info btn-sm" target="_blank">View PDF</a>
                                    <a href="view_sgpa.php?usn=<?php echo urlencode($usn); ?>&department=<?php echo urlencode($department); ?>&scheme=<?php echo urlencode($scheme); ?>&semester=<?php echo urlencode($semester); ?>" class="btn btn-primary btn-sm">View SGPAs</a>
                                </div>
                            </li>
                        <?php }
                    } else {
                        echo "<li class='list-group-item text-center'>No PDFs uploaded.</li>";
                    }
                } else {
                    echo "<li class='list-group-item text-danger text-center'>Invalid directory.</li>";
                }
                ?>
            </ul>
        <?php } else { 
            $sql = "SELECT s1.StudentName, s1.Semester, s1.SGPA 
                    FROM sgpa_data s1
                    WHERE s1.Usn = ? 
                    AND s1.Semester <= ? 
                    AND s1.id = (SELECT MAX(s2.id) FROM sgpa_data s2 WHERE s2.Usn = s1.Usn AND s2.Semester = s1.Semester)
                    ORDER BY CAST(s1.Semester AS UNSIGNED) ASC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $usn, $semester);
            $stmt->execute();
            $result = $stmt->get_result();

            $student_name = "";
            $sgpa_data = [];

            while ($row = $result->fetch_assoc()) {
                if (isset($row['StudentName']) && !$student_name) {
                    $student_name = $row['StudentName'];
                }
                if (isset($row['Semester']) && isset($row['SGPA'])) {
                    $sgpa_data[] = $row;
                }
            }
            $stmt->close();
        ?>
            <div class="header-main"> <?php echo htmlspecialchars($student_name); ?> </div>
            <div class="header-sub"> Department: <?php echo htmlspecialchars($department); ?> | USN: <?php echo htmlspecialchars($usn); ?> </div>

            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Semester</th>
                        <th>SGPA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sgpa_data)) {
                        foreach ($sgpa_data as $row) { ?>
                            <tr>
                                <td><?php echo $row['Semester']; ?></td>
                                <td><?php echo $row['SGPA']; ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="2" class="text-center">No SGPA data found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
