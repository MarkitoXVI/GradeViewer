<?php
include 'config.php';
if ($_SESSION['role'] !== 'teacher') header("Location: index.php");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        ':first_name' => sanitizeInput($_POST['first_name']),
        ':last_name' => sanitizeInput($_POST['last_name']),
        ':username' => sanitizeInput($_POST['username']),
        ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
    ];

    try {
        $stmt = $conn->prepare("INSERT INTO Students (first_name, last_name, username, password) 
                              VALUES (:first_name, :last_name, :username, :password)");
        $stmt->execute($data);
        header("Location: manage_students.php");
    } catch(PDOException $e) {
        $error = "Lietotājvārds jau eksistē!";
    }
}
?>

<!-- Forma ar Bootstrap validāciju -->
<form method="POST" class="container mt-4">
    <div class="mb-3">
        <label>Vārds</label>
        <input type="text" name="first_name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Uzvārds</label>
        <input type="text" name="last_name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Lietotājvārds</label>
        <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Parole</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <button type="submit" class="btn btn-primary">Saglabāt</button>
</form>