<?php
include 'config.php';

// Pārbaude vai skolotājs ir pieslēdzies
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Dzēšanas funkcionalitāte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM Subjects WHERE ID = ?");
        $stmt->execute([$_POST['delete_id']]);
        $_SESSION['success'] = "Priekšmets veiksmīgi dzēsts!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Nevar dzēst priekšmetu, kam pievienotas atzīmes!";
    }
    header("Location: manage_subjects.php");
    exit();
}

// Iegūstam visus priekšmetus ar sakārtotu sarakstu
$stmt = $conn->query("SELECT * FROM Subjects ORDER BY subject_name ASC");
$subjects = $stmt->fetchAll();

// Ziņu izvade
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

include 'header.php';
?>

<div class="container mt-4">
    <h2>Priekšmetu pārvaldība</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="add_subject.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Pievienot jaunu priekšmetu
        </a>
    </div>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Priekšmeta nosaukums</th>
                <th>Darbības</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subjects as $subject): ?>
            <tr>
                <td><?= htmlspecialchars($subject['ID']) ?></td>
                <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                <td>
                    <a href="edit_subject.php?id=<?= $subject['ID'] ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Labot
                    </a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $subject['ID'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" 
                            onclick="return confirm('Vai tiešām vēlaties dzēst šo priekšmetu?')">
                            <i class="bi bi-trash"></i> Dzēst
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if (empty($subjects)): ?>
        <div class="alert alert-info">Nav pievienotu priekšmetu</div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>