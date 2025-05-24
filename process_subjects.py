import sys
import pdfplumber
import re
import pandas as pd
import matplotlib.pyplot as plt
from sklearn.feature_extraction.text import CountVectorizer
from sklearn.svm import SVC
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score
import numpy as np

# Training Data: Labeled as "Difficult" (1) or "Easy" (0)
def train_svm_classifier(csv_path):
    data = pd.read_csv(csv_path)
    data.rename(columns={'algorithm': 'Text', '1': 'Label'}, inplace=True)

    if "Text" not in data.columns or "Label" not in data.columns:
        raise ValueError("The dataset must have 'Text' and 'Label' columns.")

    vectorizer = CountVectorizer()
    X = vectorizer.fit_transform(data["Text"])
    y = data["Label"]

    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    model = SVC(kernel='linear', probability=True)
    model.fit(X_train, y_train)

    y_pred = model.predict(X_test)
    accuracy = accuracy_score(y_test, y_pred)
    print(f"SVM Model Accuracy: {accuracy * 100:.2f}%")

    return model, vectorizer

# Train the model and vectorizer
csv_path = ""  # Update path to your CSV file
svm_model, vectorizer = train_svm_classifier(csv_path)

def calculate_difficulty(text):
    modules = text.split('Module')
    difficulty_score = 0
    for module in modules:
        words = module.split()
        if not words:
            continue
        word_features = vectorizer.transform(words)
        predictions = svm_model.predict(word_features)
        difficulty_score += np.sum(predictions)
    return difficulty_score

def extract_subject_data_with_names(pdf_path):
    with pdfplumber.open(pdf_path) as pdf:
        text = "".join([page.extract_text() for page in pdf.pages])

    subject_code_pattern = r'\b21[A-Z]{2,3}\d{2,3}\b'
    matches = list(re.finditer(subject_code_pattern, text))

    subject_data = []
    lines = text.splitlines()
    subject_name_set = set()

    for i, match in enumerate(matches):
        subject_code = match.group()
        start_index = match.start()

        subject_name = "Unknown"
        preceding_lines = text[:start_index].splitlines()

        for line_index in range(len(preceding_lines) - 1, -1, -1):
            line = preceding_lines[line_index].strip()
            if line and re.match(r'^[A-Z0-9\s,.\-:()&+#]+$', line) and line not in {"CIE MARKS", "SEE MARKS", "CREDITS", "EXAM HOURS", "COURSE OBJECTIVES"}:
                subject_name = line
                break

        if subject_name == "Unknown" or subject_name in subject_name_set:
            subject_name = "Unknown"
        else:
            subject_name_set.add(subject_name)

        if i < len(matches) - 1:
            next_start_index = matches[i + 1].start()
            syllabus_text = text[start_index:next_start_index].strip()
        else:
            syllabus_text = text[start_index:].strip()

        if subject_code not in [data[0] for data in subject_data]:
            subject_data.append((subject_code, subject_name, syllabus_text))

    subject_df = pd.DataFrame(subject_data, columns=["Subject Code", "Subject Name", "Syllabus Text"]).drop_duplicates(subset="Subject Code")
    subject_df["Subject Name"] = subject_df["Subject Name"].replace("", "Unknown")
    return subject_df

def roman_to_int(roman):
    roman_map = {'I': 1, 'II': 2, 'III': 3, 'IV': 4, 'V': 5, 'VI': 6, 'VII': 7, 'VIII': 8}
    return roman_map.get(roman.upper(), None)

def classify_subjects_by_semester(subject_data):
    semester_mapping = {3: [], 4: [], 5: [], 6: [], 7: [], 8: []}

    for _, row in subject_data.iterrows():
        code = row["Subject Code"]
        name = row["Subject Name"]
        syllabus_text = row["Syllabus Text"]

        semester_match = re.match(r'21[A-Z]{2,3}(\d)', code)
        if semester_match:
            semester = int(semester_match.group(1))
            if semester in semester_mapping:
                difficulty_score = calculate_difficulty(syllabus_text)
                semester_mapping[semester].append((code, name, difficulty_score))

    for semester in semester_mapping:
        semester_mapping[semester].sort(key=lambda x: x[2], reverse=True)
        for rank, item in enumerate(semester_mapping[semester], start=1):
            semester_mapping[semester][rank - 1] = (item[0], item[1], rank)

    return semester_mapping

import os  # Add this import for directory operations

def display_semester_graph(semester, subjects):
    codes = [item[0] for item in subjects]
    scores = [item[2] for item in subjects]

    plt.figure(figsize=(10, 6))
    plt.barh(codes, scores, color='skyblue')
    plt.title(f"Semester {semester} - Course Ranking")
    plt.xlabel("Ranking (Higher = More Difficult)")
    plt.ylabel("Subject Codes")
    plt.gca().invert_yaxis()
    plt.tight_layout()

    # Ensure the directory exists
    graph_dir = "graphs"
    graph_path = os.path.join("C:\\wamp64\\www\\project\\templates", graph_dir)
    os.makedirs(graph_path, exist_ok=True)  # Create the directory if it doesn't exist

    graph_filename = os.path.join(graph_path, f"semester_{semester}_graph.png")
    plt.savefig(graph_filename)
    plt.close()

    return graph_filename


# Fetch the file and semester from the command-line arguments
pdf_path = sys.argv[1]
selected_semester = sys.argv[2]
# Convert selected semester
selected_semester = roman_to_int(selected_semester)
if selected_semester is None:
    raise ValueError(f"Invalid semester format: {sys.argv[2]}")

# Process the PDF file
subject_data = extract_subject_data_with_names(pdf_path)
semester_mapping = classify_subjects_by_semester(subject_data)
subjects = semester_mapping.get(selected_semester, [])

# Generate the graph
graph_path = display_semester_graph(selected_semester, subjects)

# Python script output includes the graph path
print(graph_path)

# Text output for the most and least difficult subjects
most_difficult_subject = subjects[0] if subjects else ("", "Unknown")
least_difficult_subject = subjects[-1] if subjects else ("", "Unknown")

most_difficult_text = f"The most challenging subject in Semester {selected_semester} is '{most_difficult_subject[1]}' (Subject Code: {most_difficult_subject[0]}). It requires significant attention and effort to achieve a good pass rate."
least_difficult_text = f"The least challenging subject is '{least_difficult_subject[1]}' (Subject Code: {least_difficult_subject[0]}). Allocating minimal study time to this subject is sufficient."

print(f"Selected semester (numeric): {selected_semester}")
print(f"Subjects in selected semester: {subjects}")

print(most_difficult_text)
print(least_difficult_text)
