📊 Result Analysis System
A smart automation tool that extracts student results from PDF files, stores them in a MySQL database, and generates categorized Excel reports with insightful dashboards and email notifications — built to simplify academic result processing.

🚀 Features:
      📄 PDF Data Extraction: Automatically parses PDFs to retrieve USNs, subject codes, and marks.
      
      📊 Performance Categorization: Classifies results into FCD, FC, SC, and Fail categories.
      
      🏅 Top Performer Analysis: Identifies subject-wise toppers and overall best performers.
      
      📈 Subject Difficulty Insights: Generates graphs for subject-wise performance trends.
      
      📥 Excel Report Generation: Structured and formatted Excel outputs for each department.
      
      🖥️ User Interface: Dashboard using Flask for admins and department-level users.
      
      📧 Email Notifications: Sends automated result summaries and updates to stakeholders.

🛠️ Tech Stack
Backend: Python (PDF parsing, logic, automation)

Database: MySQL

Frontend: HTML, CSS, PHP

Libraries: openpyxl, PyPDF2, Flask, matplotlib, smtplib

📁 Project Structure

wamp64/www/project/

│

├── .venv/                    # Virtual environment (Python)

├── instance/trial.db         # Your Database which you want to use/instance folder

├── static/                   # Static assets (CSS, JS)

├── templates/                # Main logic and interface files

│   ├── excel/                # Generated Excel reports (org by dept/scheme/sem)

│   ├── uploads/              # Uploaded student PDFs (org by dept/scheme/sem)

│   ├── graphs/, syllabus/    # Charts and syllabus data

│   ├── login.html, dashboard.html

│   ├── process_subjects.py, result.py  # Python logic

│   ├── upload.php, insert.php         # PHP logic

│   └── styles.css, logo.png           # Styles and branding

│

├── uploads/                 # Downloaded result sheets manually added


🚀 How It Works

1.Upload result PDF.

2.System extracts data, processes performance, and stores it in MySQL.

3.Generates categorized Excel files and graphs.

4.Displays results in dashboard and sends summary via email.

📌 Use Cases
Academic departments for semester-wise result analysis.
College admins to identify subject-wise trends and academic bottlenecks.
Performance review meetings and NAAC documentation.

📧 Contact
For queries or contributions, reach out at:
📩 khushipragna21@gmail.com


![Screenshot 2025-04-29 161545](https://github.com/user-attachments/assets/85c6d8a6-4341-485c-a296-9647a0b38560)
![Screenshot 2025-04-29 161442](https://github.com/user-attachments/assets/10bd3422-3283-4ac8-b006-3f593478fe87)
![Screenshot 2025-04-29 161339](https://github.com/user-attachments/assets/d930eae5-8315-41fc-88da-b0e97b53e436)
![Screenshot 2025-04-29 161310](https://github.com/user-attachments/assets/ba285349-c145-4e18-bfc2-fcfa171552d6)
![Screenshot 2025-04-29 160716](https://github.com/user-attachments/assets/7a44298d-3a33-4b0c-acfe-6cbfbfe64d42)
![Screenshot 2025-04-29 160431](https://github.com/user-attachments/assets/3faf48a1-1cf3-4fdc-8b02-e22301049ac6)
