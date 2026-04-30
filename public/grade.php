<?php
require '../config/session_check.php';
require '../config/db.php';
checkRole('teacher');

if (isset($_POST['grade'])) {
    $sub_id = $_POST['sub_id'];
    $grade = $_POST['grade'];

    $stmt = $pdo->prepare("UPDATE submissions SET grade = ? WHERE id = ?");
    $stmt->execute([$grade, $sub_id]);

    header("Location: " . $_SERVER['HTTP_REFERER']);
}