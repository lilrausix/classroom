<?php
require '../config/session_check.php';
require '../config/db.php';
checkRole('pupil');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['assignment_id'])) {
    header('Location: pupil.php');
    exit();
}

requireCsrf();
$assignmentId = (int) $_POST['assignment_id'];
$pupilId = $_SESSION['user_id'];
$comment = trim($_POST['comment'] ?? '');

$submissionFile = null;
if (!empty($_FILES['task_file']['tmp_name'])) {
    $uploadDir = __DIR__ . '/../uploads/submissions/';
    $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'txt', 'png', 'jpg', 'jpeg'];

    $fileName = saveUploadedFile($_FILES['task_file'], $uploadDir, $allowedExtensions);
    if ($fileName === false) {
        die('Nederīgs faila tips. Atļautie formāti: pdf, doc, docx, ppt, pptx, xls, xlsx, zip, txt, png, jpg, jpeg.');
    }

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
