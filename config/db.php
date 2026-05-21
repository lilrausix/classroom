<?php
$host = 'localhost';
$dbname = 'projekts_db';
$user = 'root';
$pass = ''; // Laragon noklusējuma parole ir tukša

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Ensure helper tables exist for the application features.
    $pdo->exec("CREATE TABLE IF NOT EXISTS class_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        pupil_id INT NOT NULL,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_member (class_id, pupil_id),
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        FOREIGN KEY (pupil_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS action_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT,
        action_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>