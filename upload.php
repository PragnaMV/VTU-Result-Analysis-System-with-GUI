<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";#your password
$dbname = "";#your dbname

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form fields
    $department = $_POST['department'] ?? '';
    $scheme = $_POST['scheme'] ?? '';
    $year = $_POST['year'] ?? '';

    // Check if required fields are empty
    if (empty($department) || empty($scheme) || empty($year)) {
        die("Error: All fields are required.");
    }

    // Insert form data into the database
    $sql = "INSERT INTO uploads (department, scheme, year) VALUES ('$department', '$scheme', '$year')";
    if ($conn->query($sql) !== TRUE) {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Handle file uploads
    if (!empty($_FILES['file-upload']['name'][0])) {
        // Loop through all uploaded files
        foreach ($_FILES['file-upload']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['file-upload']['name'][$key];
            $file_tmp = $_FILES['file-upload']['tmp_name'][$key];
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Check if the file is a PDF
            if ($file_extension === 'pdf') {
                // Construct the directory path
                $upload_dir = "uploads/$department/$scheme/$year/";

                // Create the upload directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Move uploaded file to the directory
                if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                    // File uploaded successfully
                } else {
                    echo "Failed to upload: $file_name <br>";
                }
            } else {
                echo "Skipped non-PDF file: $file_name <br>";
            }
        }
    } else {
        echo "No files selected for upload.";
    }

    // Redirect back to the dashboard after the upload process
    header("Location: dashboard.html");
    exit();  // Always call exit after header to stop the script execution
}

$conn->close();
?>
