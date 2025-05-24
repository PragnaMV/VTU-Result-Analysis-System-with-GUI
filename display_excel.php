<?php
// Get parameters from the URL
$department = $_GET['department'] ?? '';
$scheme = $_GET['scheme'] ?? '';
$semester = $_GET['semester'] ?? '';

// Sanitize inputs (basic security)
$department = htmlspecialchars($department);
$scheme = htmlspecialchars($scheme);
$semester = htmlspecialchars($semester);

// Construct the file path
$excelFilePath = "C:\\wamp64\\www\\project\\templates\\excel\\$department\\$scheme\\$semester\\{$department}_{$scheme}_{$semester}SEM.xlsx";

// Check if file exists
if (!file_exists($excelFilePath)) {
    die("Excel file not found at: $excelFilePath");
}

// Load Excel file using PhpSpreadsheet
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load($excelFilePath);
$sheets = $spreadsheet->getAllSheets();

$sheetDataArray = [];
$sheetNames = [];

foreach ($sheets as $sheet) {
    $sheetNames[] = $sheet->getTitle();
    $sheetDataArray[] = $sheet->toArray();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students Report Sheet</title>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #004488; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #ddd; }
        .center { text-align: center; }
        .info { text-align: center; font-size: 20px; margin-top: 10px; }
        button { margin: 5px; padding: 5px 10px; font-size: 14px; cursor: pointer; }
    </style>
</head>
<body>
    <h2 class='center'>Students Report Sheet</h2>
    <div class='info'>
        <strong>Department:</strong> <?php echo $department; ?> |
        <strong>Scheme:</strong> <?php echo $scheme; ?> |
        <strong>Semester:</strong> <?php echo $semester; ?>
    </div>

    <div id="sheets-container"></div>

    <div class="center">
        <button onclick="exportTableToExcel()">Export Edited Excel</button>
    </div>

    <script>
        const allSheetData = <?php echo json_encode($sheetDataArray); ?>;
        const allSheetNames = <?php echo json_encode($sheetNames); ?>;
        let globalSheetJson = [];
        let globalSheetName = "";

        const container = document.getElementById('sheets-container');

        allSheetData.forEach((sheetData, index) => {
            const heading = document.createElement('h3');
            heading.classList.add('center');
            heading.textContent = "Sheet: " + allSheetNames[index];
            container.appendChild(heading);

            globalSheetJson = sheetData;
            globalSheetName = allSheetNames[index];

            const table = createTableWithEdit(globalSheetJson);
            container.appendChild(table);
        });

        function createTableWithEdit(data) { 
            const table = document.createElement('table');
            const headerRow = document.createElement('tr');
            data[0].forEach(header => {
                const th = document.createElement('th');
                th.textContent = header;
                headerRow.appendChild(th);
            });

            const actionTh = document.createElement('th');
            actionTh.textContent = 'Actions';
            headerRow.appendChild(actionTh);
            table.appendChild(headerRow);

            data.slice(1).forEach((row, rowIndex) => {
                const tr = document.createElement('tr');
                row.forEach(cell => {
                    const td = document.createElement('td');
                    td.textContent = cell !== undefined ? cell : '';
                    td.setAttribute('contenteditable', 'false');
                    tr.appendChild(td);
                });

                const actionTd = document.createElement('td');
                const editBtn = document.createElement('button');
                editBtn.textContent = 'Edit';
                editBtn.addEventListener('click', () => enableRowEdit(tr, saveBtn));

                const saveBtn = document.createElement('button');
                saveBtn.textContent = 'Save';
                saveBtn.disabled = true;
                saveBtn.addEventListener('click', () => saveRowEdit(tr, rowIndex + 1, saveBtn, editBtn));

                actionTd.appendChild(editBtn);
                actionTd.appendChild(saveBtn);
                tr.appendChild(actionTd);
                table.appendChild(tr);
            });
            return table;
        }

        function enableRowEdit(tr, saveBtn) {
            const tds = tr.querySelectorAll('td:not(:last-child)');
            tds.forEach(td => {
                td.setAttribute('contenteditable', 'true');
                td.style.backgroundColor = '#f1f1f1';
            });
            saveBtn.disabled = false;
        }

        function saveRowEdit(tr, dataRowIndex, saveBtn, editBtn) {
            const tds = tr.querySelectorAll('td:not(:last-child)');
            const updatedRow = [];
            tds.forEach(td => {
                updatedRow.push(td.textContent);
                td.setAttribute('contenteditable', 'false');
                td.style.backgroundColor = '';
            });
            globalSheetJson[dataRowIndex] = updatedRow;
            saveBtn.disabled = true;
            editBtn.disabled = false;
        }

        function exportTableToExcel() {
            if (!globalSheetJson) {
                alert("No data available to export.");
                return;
            }
            const ws = XLSX.utils.aoa_to_sheet(globalSheetJson);
            const newWorkbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(newWorkbook, ws, globalSheetName);
            const wbout = XLSX.write(newWorkbook, { bookType: 'xlsx', type: 'array' });
            const blob = new Blob([wbout], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = "Edited_Report.xlsx";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    </script>
</body>
</html>
