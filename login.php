<?php
include 'config.php';

$username = sanitizeInput($_POST['username']);
$password = sanitizeInput($_POST['password']);

// Pārbaude skolotājiem
$stmt = $conn->prepare("SELECT * FROM Teachers WHERE username = ?");
$stmt->execute([$username]);
if($teacher = $stmt->fetch()) {
    if(password_verify($password, $teacher['password'])) {
        $_SESSION['user_id'] = $teacher['ID'];
        $_SESSION['role'] = 'teacher';
        header("Location: teacher_dashboard.php");
        exit();
    }
}

// Pārbaude studentiem
$stmt = $conn->prepare("SELECT * FROM Students WHERE username = ?");
$stmt->execute([$username]);
if($student = $stmt->fetch()) {
    if(password_verify($password, $student['password'])) {
        $_SESSION['user_id'] = $student['ID'];
        $_SESSION['role'] = 'student';
        header("Location: student_grades.php");
        exit();
    }
}

header("Location: index.php?error=invalid_credentials");