<?php
// Include PHPExcel library (Ensure it's installed via Composer or manually included)
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Database connection
$servername = "localhost";
$username = "root";
$password = "";#your password
$dbname = "";#your dbname

// Get input parameters
$department = $_GET['department'];
$scheme = $_GET['scheme'];
$semester = (int) $_GET['semester'];  // Ensure it's an integer

// Construct file path
$filePath = "C:\\wamp64\\www\\project\\templates\\excel\\$department\\$scheme\\$semester\\{$department}_{$scheme}_{$semester}SEM.xlsx";

// Check if file exists
if (!file_exists($filePath)) {
    die("Error: File not found at $filePath");
}

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Load Excel file
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

// Get last row index
$lastRow = count($data);

// Extract column positions for SGPA and Total Credits
$totalCreditsCol = count($data[0]) - 6;
$sgpaCol = count($data[0]) - 5;
$semesterCol = 0;

// Ensure CGPA table exists with Department column
$conn->query("CREATE TABLE IF NOT EXISTS CGPA (
    Usn VARCHAR(20),
    StudentName VARCHAR(100),
    Department VARCHAR(100),
    Semester INT,
    Scheme INT,
    SGPA FLOAT,
    TotalCredits INT,
    CGPA FLOAT DEFAULT NULL,
    PRIMARY KEY (Usn, Semester)
)");

// Process each row (skip header)
for ($i = 1; $i < $lastRow; $i++) {
    $semesterFromExcel = (int) $data[$i][$semesterCol];  // Read semester
    $usn = $data[$i][1];  // USN
    $studentName = $data[$i][2];  // Student name
    $sgpa = round((float) $data[$i][$sgpaCol], 2);
    $totalCredits = (int) $data[$i][$totalCreditsCol];

    // Insert or update data with Department
    $sql = "INSERT INTO CGPA (Usn, StudentName, Department, Semester, Scheme, SGPA, TotalCredits, CGPA)
            VALUES ('$usn', '$studentName', '$department', $semesterFromExcel, $scheme, ROUND($sgpa,2), $totalCredits, NULL)
            ON DUPLICATE KEY UPDATE SGPA=ROUND($sgpa,2), TotalCredits=$totalCredits, Department='$department'";
    $conn->query($sql);
}

// Update CGPA for each student
$sql = "SELECT DISTINCT Usn FROM CGPA";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $usn = $row['Usn'];
    
    // Compute CGPA using weighted sum formula
    $cgpaQuery = "SELECT SUM(SGPA * TotalCredits) AS total_sgpa, SUM(TotalCredits) AS total_credits FROM CGPA WHERE Usn = '$usn' AND Semester <= $semester";
    $cgpaResult = $conn->query($cgpaQuery);
    $cgpaData = $cgpaResult->fetch_assoc();

    $total_sgpa = $cgpaData['total_sgpa'];
    $total_credits = $cgpaData['total_credits'];
    $cgpa = $total_credits > 0 ? round($total_sgpa / $total_credits, 2) : 0;

    $updateQuery = "UPDATE CGPA SET CGPA = $cgpa WHERE Usn = '$usn' AND Semester <= $semester";
    $conn->query($updateQuery);
}

// Fetch data for display
$sql = "SELECT Usn, StudentName, 
    MAX(CASE WHEN Semester = 1 THEN SGPA END) AS Sem1,
    MAX(CASE WHEN Semester = 2 THEN SGPA END) AS Sem2,
    MAX(CASE WHEN Semester = 3 THEN SGPA END) AS Sem3,
    MAX(CASE WHEN Semester = 4 THEN SGPA END) AS Sem4,
    MAX(CASE WHEN Semester = 5 THEN SGPA END) AS Sem5,
    MAX(CASE WHEN Semester = 6 THEN SGPA END) AS Sem6,
    MAX(CASE WHEN Semester = 7 THEN SGPA END) AS Sem7,
    MAX(CASE WHEN Semester = 8 THEN SGPA END) AS Sem8,
    MAX(CGPA) AS Final_CGPA 
    FROM CGPA 
    WHERE Semester <= $semester 
      AND Department = '$department' 
      AND Scheme = $scheme
    GROUP BY Usn, StudentName";

$result = $conn->query($sql);

// Display data in styled table
echo "<style>
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #004488; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #ddd; }
        .center { text-align: center; }
        .info { text-align: center; font-size: 20px; margin-top: 10px; }
    </style>";

echo "<h2 class='center'>SGPA Report with CGPA</h2>";
echo "<div class='info'>
        <strong>Department:</strong> " . htmlspecialchars($department) . " | 
        <strong>Scheme:</strong> " . htmlspecialchars($scheme) . " | 
        <strong>Semester:</strong> " . htmlspecialchars($semester) . "
      </div>";

echo "<table>";
echo "<tr>
        <th>USN</th>
        <th>Student Name</th>";
for ($i = 1; $i <= $semester; $i++) {
    echo "<th>{$i} Sem</th>";  // ✅ Only show headers up to the selected semester
}
echo "<th>CGPA</th></tr>";

while ($row = $result->fetch_assoc()) { 
    echo "<tr>
            <td>{$row['Usn']}</td>
            <td>{$row['StudentName']}</td>";
    
    // ✅ Dynamically display only selected semesters
    for ($i = 1; $i <= $semester; $i++) {
        $semKey = "Sem" . $i;
        echo "<td>" . (isset($row[$semKey]) ? number_format($row[$semKey], 2) : 'N/A') . "</td>";
    }

    echo "<td>" . number_format($row['Final_CGPA'], 2) . "</td>
          </tr>";
}

echo "</table>";
$conn->close();
?>
