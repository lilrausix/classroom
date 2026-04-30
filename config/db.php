<?php
$host = 'localhost';
$dbname = 'projekts_db';
$user = 'root';
$pass = ''; // Laragon noklusējuma parole ir tukša

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>