<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funkcija, kas pārbauda vai lietotājs ir ielogojies un vai viņam ir pareizā loma
function checkRole($allowed_role) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    if ($_SESSION['role'] !== $allowed_role) {
        die("<h1 style='color:white; background:#0f172a; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'>
            403 - Piekļuve liegta. Tev nav $allowed_role tiesību.
            </h1>");
    }
}
?>