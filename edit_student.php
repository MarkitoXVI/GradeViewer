<?php
include 'config.php';
if ($_SESSION['role'] !== 'teacher') header("Location: index.php");

// Iegūstam studenta datus
$stmt = $conn->prepare("SELECT * FROM Students WHERE ID = ?");
$stmt->execute([$_GET['id']]);
$student = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        ':first_name' => sanitizeInput($_POST['first_name']),
        ':last_name' => sanitizeInput($_POST['last_name']),
        ':username' => sanitizeInput($_POST['username']),
        ':id' => $_GET['id']
    ];

    // Paroles atjaunināšana tikai ja ievadīta
    if (!empty($_POST['password'])) {
        $data[':password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE Students SET first_name=:first_name, last_name=:last_name, 
                username=:username, password=:password WHERE ID=:id";
    } else {
        $sql = "UPDATE Students SET first_name=:first_name, last_name=:last_name, 
                username=:username WHERE ID=:id";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
    header("Location: manage_students.php");
}
?>

<!-- Rediģēšanas forma ar esošajiem datiem -->
<form method="POST" class="container mt-4">
    <input type="text" name="first_name" value="<?= $student['first_name'] ?>" required>
    ...
</form>