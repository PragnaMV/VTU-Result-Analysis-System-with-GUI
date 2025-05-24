<center>
<head>
    <link rel="stylesheet" href="http://localhost/project/templates/styles.css">
</head>

<?php
// Check if form data was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the department, scheme, and year from the POST request
    $department = isset($_POST['department']) ? $_POST['department'] : '';
    $scheme = isset($_POST['scheme']) ? $_POST['scheme'] : '';
    $year = isset($_POST['year']) ? $_POST['year'] : '';


    // Define the base path where the Excel sheets are stored
    $basePath = "C:\\wamp64\\www\\project\\templates\\excel\\";

    // Build the directory path based on the user selection
    $dirPath = $basePath . $department . '\\' . $scheme . '\\' . $year;

    // Check if the directory exists
    if (is_dir($dirPath)) {
        // Open the directory and get all files
        $files = scandir($dirPath);

        // Filter out files that are not Excel files
        $excelFiles = array_filter($files, function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'xlsx';
        });

        // If no files are found
        if (empty($excelFiles)) {
            echo "No Excel files found for the selected criteria.";
        } else {
            // Display the Excel files as links
            echo "<h1>Available Excel Files:</h1>";

            // Start the table
            echo "<table border='1' cellpadding='10' cellspacing='0'>";
            echo "<tr><th>Department</th><th>Scheme</th><th>Year</th><th>File</th></tr>";

            // Loop through the files and display each as a table row
            foreach ($excelFiles as $file) {
                echo "<tr>";
                echo "<td>" . $department . "</td>";
                echo "<td>" . $scheme . "</td>";
                echo "<td>" . $year . "</td>";
                echo "<td><a href='/project/templates/excel/" . $department . "/" . $scheme . "/" . $year . "/" . $file . "' target='_blank'>" . $file . "</a></td>";
                echo "</tr>";
            }

            // End the table
            echo "</table>";


        }
    } else {
        echo "Invalid directory structure or no files found.";
    }
    
}
?></center>
