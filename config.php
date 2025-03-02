<?php
session_start();
$host = "localhost";
$username = "root"; // Adjust if needed
$password = ""; // Adjust if needed
$database = "gis_insights";

$db = mysqli_connect($host, $username, $password, $database);
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database and tables if they don’t exist
$create_db = "CREATE DATABASE IF NOT EXISTS $database";
mysqli_query($db, $create_db);
mysqli_select_db($db, $database);

$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        country VARCHAR(50),
        is_student ENUM('yes', 'no'),
        university VARCHAR(100),
        non_student_role VARCHAR(100),
        agree_terms TINYINT(1) NOT NULL,
        referral_source VARCHAR(50),
        referral_details TEXT
    )",
    "CREATE TABLE IF NOT EXISTS insights (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        insight TEXT NOT NULL,
        latitude FLOAT NOT NULL,
        longitude FLOAT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS portfolio (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        work_path VARCHAR(255) NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS partnership_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(100) NOT NULL,
        contact_email VARCHAR(100) NOT NULL,
        message TEXT,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS explore_content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        type ENUM('video', 'image') NOT NULL,
        url VARCHAR(255) NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $table) {
    mysqli_query($db, $table);
}
?>