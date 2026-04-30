CREATE DATABASE IF NOT EXISTS projekts_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projekts_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'pupil') DEFAULT 'pupil',
    avatar VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    join_code VARCHAR(10) UNIQUE NOT NULL,
    teacher_id INT,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255), -- Skolotāja pievienotais fails
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE class_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    pupil_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_member (class_id, pupil_id),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (pupil_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT,
    pupil_id INT,
    file_path VARCHAR(255), -- Skolēna augšupielādētais fails
    comment TEXT,
    grade INT DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (pupil_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE action_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

INSERT INTO users (name, email, password, role) VALUES 
('Admin Lietotājs', 'admin@edu.lv', '$2y$10$1k2aM9OsXIzOpzMLjhRDw.EpAQWXYHW8HZiWRnGNveEEKjw0otcpq', 'admin'),
('Skolotājs Kārlis', 'karlis@edu.lv', '$2y$10$1k2aM9OsXIzOpzMLjhRDw.EpAQWXYHW8HZiWRnGNveEEKjw0otcpq', 'teacher'),
('Skolēns Jānis', 'janis@edu.lv', '$2y$10$1k2aM9OsXIzOpzMLjhRDw.EpAQWXYHW8HZiWRnGNveEEKjw0otcpq', 'pupil');