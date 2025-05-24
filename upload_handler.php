<?php
// Database connection (adjust with your own details)
$host = 'localhost';
$dbname = '';  // Replace with your database name
$username = 'root';  // Replace with your database username
$password = '';  // Replace with your database password

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload and processing
    if (isset($_FILES['file-upload']) && isset($_POST['semester'])) {
        $semester = htmlspecialchars($_POST['semester']);
        $uploadDir = 'uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedFile = $_FILES['file-upload'];
        $fileName = basename($uploadedFile['name']);
        $filePath = $uploadDir . $fileName;

        // Validate file extension
        $allowedExtensions = ['pdf', 'zip'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['error' => 'Only PDF and ZIP files are allowed.']);
            exit;
        }

        // Move the uploaded file
        if (move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
            // Run the Python script to process the file
            $python_path = '';  // Adjust this path if needed
			$script_path = ''; // Adjust this path if needed
			// Prepare the command to run the Python script with arguments
			$site_packages = '';// your path to site packages on local machine

			// Set PYTHONPATH to include the correct site-packages
			$command = 'set PYTHONPATH=' . $site_packages . ' && ' . $python_path . ' ' . $script_path . ' ' . escapeshellarg($filePath) . ' ' . escapeshellarg($semester);
            $output = shell_exec($command . " 2>&1");

            if ($output === null) {
                echo json_encode(['error' => 'Unable to execute the script.']);
            } else {
                // Parse Python output to get relevant data
                $lines = explode("\n", $output);
                $most_difficult = $lines[2];
                $least_difficult = $lines[3];
                $subjects = implode(', ', array_slice($lines, 4));

                // Insert results into the database
                $stmt = $pdo->prepare("INSERT INTO analysis_results (semester, most_difficult_subject, least_difficult_subject, subjects, uploaded_file) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$semester, $most_difficult, $least_difficult, $subjects, $fileName]);

                // Return success response
                echo json_encode([
                    'success' => true,
                    'most_difficult' => $most_difficult,
                    'least_difficult' => $least_difficult,
                    'subjects' => $subjects
                ]);
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'dashboard.html';
                        }, 10000);
                      </script>";
            }
        } else {
            echo json_encode(['error' => 'File upload failed.']);
        }
    } else {
        echo json_encode(['error' => 'Please provide both a file and select a semester.']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch semester outputs based on selected semester
    if (isset($_GET['semester'])) {
        $semester = htmlspecialchars($_GET['semester']);

        // Fetch results for the selected semester
        try {
            $stmt = $pdo->prepare("SELECT most_difficult_subject, least_difficult_subject, subjects FROM analysis_results WHERE semester = ?");
            $stmt->execute([$semester]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                // Return results as JSON
                echo json_encode([
                    'success' => true,
                    'most_difficult' => $result['most_difficult_subject'],
                    'least_difficult' => $result['least_difficult_subject'],
                    'subjects' => $result['subjects']
                ]);
            } else {
                echo json_encode(['error' => 'No data found for the selected semester.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Database query failed: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Semester parameter is missing.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
