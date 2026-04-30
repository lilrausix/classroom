<?php
session_start();
require '../config/session_check.php';
require '../config/db.php';
checkRole('pupil');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['assignment_id'])) {
    header('Location: pupil.php');
    exit();
}

$assignmentId = $_POST['assignment_id'];
$pupilId = $_SESSION['user_id'];
$comment = trim($_POST['comment'] ?? '');

$submissionFile = null;
if (!empty($_FILES['task_file']['tmp_name'])) {
    $uploadDir = __DIR__ . '/../uploads/submissions/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '', $_FILES['task_file']['name']);
    move_uploaded_file($_FILES['task_file']['tmp_name'], $uploadDir . $fileName);
    $submissionFile = $fileName;
}

$existingStmt = $pdo->prepare("SELECT id, file_path FROM submissions WHERE assignment_id = ? AND pupil_id = ?");
$existingStmt->execute([$assignmentId, $pupilId]);
$existing = $existingStmt->fetch();

if ($existing) {
    if ($submissionFile) {
        $updateStmt = $pdo->prepare("UPDATE submissions SET file_path = ?, comment = ?, submitted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $updateStmt->execute([$submissionFile, $comment, $existing['id']]);
    } else {
        $updateStmt = $pdo->prepare("UPDATE submissions SET comment = ?, submitted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $updateStmt->execute([$comment, $existing['id']]);
    }
} else {
    $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, pupil_id, file_path, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$assignmentId, $pupilId, $submissionFile, $comment]);
}

header('Location: pupil.php?submitted=1');
exit();
