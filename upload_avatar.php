<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if (!in_array($_FILES['avatar']['type'], $allowed_types)) {
        die("Invalid file type. Only JPG, PNG, and GIF are allowed.");
    }

    if ($_FILES['avatar']['size'] > $max_size) {
        die("File size exceeds 2MB limit.");
    }

    $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $new_filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
    $target_path = 'uploads/' . $new_filename;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
        // Delete old avatar if exists
        $stmt = $conn->prepare("SELECT avatar FROM Teachers WHERE ID = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $old_avatar = $stmt->fetchColumn();
        
        if ($old_avatar && file_exists('uploads/'.$old_avatar)) {
            unlink('uploads/'.$old_avatar);
        }

        // Update database
        $stmt = $conn->prepare("UPDATE Teachers SET avatar = ? WHERE ID = ?");
        $stmt->execute([$new_filename, $_SESSION['user_id']]);
        header("Location: settings.php?success=avatar_updated");
    } else {
        header("Location: settings.php?error=upload_failed");
    }
    exit();
}