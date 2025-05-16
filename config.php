<?php
session_start();
$host = 'localhost';
$dbname = 'grade_system';
$user = 'root';
$pass = 'root';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>