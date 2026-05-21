<?php
require '../config/session_check.php';
require '../config/db.php';
checkRole('teacher');

if (isset($_POST['grade'])) {
    requireCsrf();
    $sub_id = (int) $_POST['sub_id'];
    $grade = (int) $_POST['grade'];

    $stmt = $pdo->prepare("UPDATE submissions SET grade = ? WHERE id = ?");
    $stmt->execute([$grade, $sub_id]);

    $redirect = $_SERVER['HTTP_REFERER'] ?? 'grading.php';
    header("Location: " . $redirect);
    exit();
}
