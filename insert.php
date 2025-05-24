<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trial";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get values from form
    $code = $_POST['code'];  // Subject Code (Pre-filled from error)
    $name = $_POST['name'];  // Manually entered
    $credits = $_POST['credits'];  // Manually entered
    $year = $_POST['year'];  // Manually entered
    $sem = $_POST['sem'];  // Manually entered
    $department = $_POST['department'];  // Hidden field
    $scheme = $_POST['scheme'];  // Hidden field

    // Auto-increment ID by finding max existing ID
    $result = $conn->query("SELECT MAX(id) AS max_id FROM subjects");
    $row = $result->fetch_assoc();
    $id = $row['max_id'] + 1;

    // Insert new subject
    $sql = "INSERT INTO subjects (id, code, name, credits, year, sem) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issiii", $id, $code, $name, $credits, $year, $sem);
    
    if ($stmt->execute()) {
        echo "<script>alert('Subject added successfully! Redirecting...');</script>";

        // Redirect to download.php to re-run result.py
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'download.php?department=" . urlencode($department) . "&scheme=" . urlencode($scheme) . "&year=" . urlencode($year) . "';
                }, 2000); // Redirect after 2 seconds
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<form action="insert.php" method="POST">
    <label>Subject Code:</label>
    <input type="text" name="code" required><br>
    
    <label>Subject Name:</label>
    <input type="text" name="name" required><br>
    
    <label>Credits:</label>
    <input type="number" name="credits" required><br>
    
    <label>Year:</label>
    <input type="number" name="year" required><br>
    
    <label>Semester:</label>
    <input type="number" name="sem" required><br>
    
    <input type="hidden" name="department" value="CSE">
    <input type="hidden" name="scheme" value="2021">
    
    <button type="submit">Insert Subject</button>
</form>
