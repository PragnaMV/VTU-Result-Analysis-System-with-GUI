<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['download'])) {
    // Get values from the form
    $department = $_POST['department'];
    $scheme = $_POST['scheme'];
    $year = $_POST['year'];


	$site_packages = '';#your site packages path on local machine
	$python_path = '';#your python path on local machine

	$command = 'set PYTHONPATH=' . $site_packages . ' && ' . $python_path . ' "C:\\wamp64\\www\\project\\templates\\result.py" ' . escapeshellarg($department) . ' ' . escapeshellarg($scheme) . ' ' . escapeshellarg($year);
	$python_output = shell_exec($command . ' 2>&1');

	echo "<pre>" . htmlspecialchars($python_output ?? "No output received", ENT_QUOTES, 'UTF-8') . "</pre>";





    
    // Redirect to dashboard after 1 minute (60 seconds) using JavaScript
    echo "<script>
            setTimeout(function() {
                window.location.href = 'dashboard.html';  // Adjust this path if necessary
            }, 30000);  // 30000 milliseconds = 0.5 minute
          </script>";
}
?>
