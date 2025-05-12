<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_grades.php");
    exit();
}

// Iegūstam atzīmes datus
$stmt = $conn->prepare("SELECT * FROM Grades WHERE ID = ?");
$stmt->execute([$_GET['id']]);
$grade = $stmt->fetch();

if (!$grade) {
    header("Location: manage_grades.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];
    $new_grade = $_POST['grade'];
    
    try {
        if ($new_grade < 1 || $new_grade > 10) {
            throw new Exception("Atzīmei jābūt no 1 līdz 10");
        }
        
        $stmt = $conn->prepare("UPDATE Grades SET 
            student_id = ?,
            subject_id = ?,
            grade = ?
            WHERE ID = ?");
        $stmt->execute([$student_id, $subject_id, $new_grade, $_GET['id']]);
        $_SESSION['success'] = "Atzīme veiksmīgi atjaunināta!";
        header("Location: manage_grades.php");
        exit();
    } catch (PDOException $e) {
        $error = "Kļūda labojot atzīmi!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Iegūstam datus dropdowniem
$students = $conn->query("SELECT * FROM Students")->fetchAll();
$subjects = $conn->query("SELECT * FROM Subjects")->fetchAll();

include 'header.php';
?>

<div class="container mt-4">
    <h2>Labot atzīmi</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Skolēns</label>
            <select name="student_id" class="form-select" required>
                <?php foreach ($students as $student): ?>
                <option value="<?= $student['ID'] ?>" 
                    <?= ($student['ID'] == $grade['student_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label>Priekšmets</label>
            <select name="subject_id" class="form-select" required>
                <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['ID'] ?>" 
                    <?= ($subject['ID'] == $grade['subject_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($subject['subject_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label>Atzīme</label>
            <input type="number" name="grade" class="form-control" 
                   value="<?= $grade['grade'] ?>" 
                   min="1" max="10" step="1" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Saglabāt izmaiņas</button>
        <a href="manage_grades.php" class="btn btn-secondary">Atcelt</a>
    </form>
</div>

<?php include 'footer.php'; ?>