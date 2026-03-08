<?php
require_once 'config.php';

// SQL to create database if it doesn't exist
$create_db_sql = "CREATE DATABASE IF NOT EXISTS video_platform";
$conn->query($create_db_sql);

// Select the database
$conn->select_db("video_platform");

// SQL to create tables
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql_video_stats = "CREATE TABLE IF NOT EXISTS video_stats (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11),
    video_id INT(11) NOT NULL,
    watched_count INT(11) DEFAULT 1,
    last_watched TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_video (user_id, video_id)
)";

$sql_user_sessions = "CREATE TABLE IF NOT EXISTS user_sessions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11),
    session_token VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Create tables
if ($conn->query($sql_users) === TRUE && 
    $conn->query($sql_video_stats) === TRUE && 
    $conn->query($sql_user_sessions) === TRUE) {
    echo "Database setup completed successfully!";
} else {
    echo "Error creating tables: " . $conn->error;
}

$conn->close();
?>
