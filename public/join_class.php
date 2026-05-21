<?php
require '../config/session_check.php';
require '../config/db.php';
checkRole('pupil');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['code'])) {
    requireCsrf();
    $code = strtoupper(trim($_POST['code']));

    $stmt = $pdo->prepare("SELECT id FROM classes WHERE join_code = ?");
    $stmt->execute([$code]);
    $class = $stmt->fetch();

    if (!$class) {
        header("Location: pupil.php?error=1");
        exit();
    }

    $memberStmt = $pdo->prepare("SELECT id FROM class_members WHERE class_id = ? AND pupil_id = ?");
    $memberStmt->execute([$class['id'], $_SESSION['user_id']]);

    if (!$memberStmt->fetch()) {
        $insert = $pdo->prepare("INSERT INTO class_members (class_id, pupil_id) VALUES (?, ?)");
        $insert->execute([$class['id'], $_SESSION['user_id']]);
    }

    header("Location: pupil.php?joined=1");
    exit();
}

header("Location: pupil.php");
exit();
