<?php
require '../config/session_check.php';
require '../config/db.php';
checkRole('teacher');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_id = $_POST['class_id'];
    $title = $_POST['title'];
    $desc = $_POST['description'];
    
    $file_path = null;
    if (!empty($_FILES['assignment_file']['name'])) {
        $file_path = time() . '_' . $_FILES['assignment_file']['name'];
        move_uploaded_file($_FILES['assignment_file']['tmp_name'], "../uploads/assignments/" . $file_path);
    }

    $stmt = $pdo->prepare("INSERT INTO assignments (class_id, title, description, file_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$class_id, $title, $desc, $file_path]);

    header("Location: class_view.php?id=" . $class_id);
}
?>