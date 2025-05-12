<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_subjects.php");
    exit();
}

// Iegūstam esošos datus
$stmt = $conn->prepare("SELECT * FROM Subjects WHERE ID = ?");
$stmt->execute([$_GET['id']]);
$subject = $stmt->fetch();

if (!$subject) {
    header("Location: manage_subjects.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name']);
    
    if (empty($subject_name)) {
        $error = "Priekšmeta nosaukums nevar būt tukšs!";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE Subjects SET subject_name = ? WHERE ID = ?");
            $stmt->execute([$subject_name, $_GET['id']]);
            $_SESSION['success'] = "Priekšmets veiksmīgi atjaunināts!";
            header("Location: manage_subjects.php");
            exit();
        } catch (PDOException $e) {
            $error = "Priekšmets ar šādu nosaukumu jau eksistē!";
        }
    }
}

include 'header.php';
?>

<div class="container mt-4">
    <h2>Labot priekšmetu</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Priekšmeta nosaukums</label>
            <input type="text" class="form-control" name="subject_name" 
                   value="<?= htmlspecialchars($subject['subject_name']) ?>" 
                   required maxlength="100">
        </div>
        <button type="submit" class="btn btn-primary">Saglabāt izmaiņas</button>
        <a href="manage_subjects.php" class="btn btn-secondary">Atcelt</a>
    </form>
</div>

<?php include 'footer.php'; ?>