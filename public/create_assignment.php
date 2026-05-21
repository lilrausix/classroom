<?php
require '../config/session_check.php';
require '../config/db.php';
checkRole('teacher');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    requireCsrf();
    $class_id = (int) $_POST['class_id'];
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    
    $file_path = null;
    if (!empty($_FILES['assignment_file']['tmp_name'])) {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'txt'];
        $file_path = saveUploadedFile($_FILES['assignment_file'], __DIR__ . '/../uploads/assignments/', $allowedExtensions);

        if ($file_path === false) {
            die('Nederīgs faila tips. Atļautie formāti: pdf, doc, docx, ppt, pptx, xls, xlsx, zip, txt.');
        }
    }

    $stmt = $pdo->prepare("INSERT INTO assignments (class_id, title, description, file_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$class_id, $title, $desc, $file_path]);

    header("Location: class_view.php?id=" . $class_id);
    exit();
}
?>