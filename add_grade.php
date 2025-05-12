<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];
    $grade = $_POST['grade'];
    
    try {
        // Validācija
        if ($grade < 1 || $grade > 10) {
            throw new Exception("Atzīmei jābūt no 1 līdz 10");
        }
        
        $stmt = $conn->prepare("INSERT INTO Grades (student_id, subject_id, grade) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $subject_id, $grade]);
        $_SESSION['success'] = "Atzīme veiksmīgi pievienota!";
        header("Location: manage_grades.php");
        exit();
    } catch (PDOException $e) {
        $error = "Kļūda pievienojot atzīmi!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Iegūstam datus dropdowniem
$students = $conn->query("SELECT * FROM Students ORDER BY last_name")->fetchAll();
$subjects = $conn->query("SELECT * FROM Subjects ORDER BY subject_name")->fetchAll();

include 'header.php';
?>

<div class="container mt-4">
    <h2>Pievienot jaunu atzīmi</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Skolēns</label>
            <select name="student_id" class="form-select" required>
                <?php foreach ($students as $student): ?>
                <option value="<?= $student['ID'] ?>">
                    <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label>Priekšmets</label>
            <select name="subject_id" class="form-select" required>
                <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['ID'] ?>">
                    <?= htmlspecialchars($subject['subject_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label>Atzīme</label>
            <input type="number" name="grade" class="form-control" 
                   min="1" max="10" step="1" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Saglabāt</button>
        <a href="manage_grades.php" class="btn btn-secondary">Atcelt</a>
    </form>
</div>

<?php include 'footer.php'; ?>