<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Dzēšanas funkcionalitāte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM Grades WHERE ID = ?");
    $stmt->execute([$_POST['delete_id']]);
    $_SESSION['success'] = "Atzīme veiksmīgi dzēsta!";
    header("Location: manage_grades.php");
    exit();
}

// Filtrācija un sakārtošana
$student_filter = $_GET['student'] ?? '';
$subject_filter = $_GET['subject'] ?? '';
$order = isset($_GET['order']) ? ($_GET['order'] === 'desc' ? 'DESC' : 'ASC') : 'ASC';

// Datu iegūšana ar JOIN
$query = "SELECT 
            Grades.ID,
            Students.first_name,
            Students.last_name,
            Subjects.subject_name,
            Grades.grade,
            Grades.date
          FROM Grades
          JOIN Students ON Grades.student_id = Students.ID
          JOIN Subjects ON Grades.subject_id = Subjects.ID
          WHERE 1=1";

$params = [];
if (!empty($student_filter)) {
    $query .= " AND (Students.first_name LIKE ? OR Students.last_name LIKE ?)";
    array_push($params, "%$student_filter%", "%$student_filter%");
}
if (!empty($subject_filter)) {
    $query .= " AND Subjects.subject_name LIKE ?";
    array_push($params, "%$subject_filter%");
}

$query .= " ORDER BY Grades.date $order";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$grades = $stmt->fetchAll();

// Iegūstam studentu un priekšmetu sarakstus filtriem
$students = $conn->query("SELECT * FROM Students")->fetchAll();
$subjects = $conn->query("SELECT * FROM Subjects")->fetchAll();

include 'header.php';
?>

<div class="container mt-4">
    <h2>Atzīmju pārvaldība</h2>
    
    <!-- Filtrēšanas forma -->
    <form class="row g-3 mb-4">
        <div class="col-md-4">
            <select class="form-select" name="student">
                <option value="">Visi skolēni</option>
                <?php foreach ($students as $student): ?>
                <option value="<?= $student['ID'] ?>" <?= ($student_filter == $student['ID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-select" name="subject">
                <option value="">Visi priekšmeti</option>
                <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['ID'] ?>" <?= ($subject_filter == $subject['ID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($subject['subject_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="order">
                <option value="asc" <?= ($order == 'ASC') ? 'selected' : '' ?>>Jaunākās augošā</option>
                <option value="desc" <?= ($order == 'DESC') ? 'selected' : '' ?>>Jaunākās dilstošā</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Filtrēt</button>
        </div>
    </form>

    <a href="add_grade.php" class="btn btn-success mb-3">Pievienot jaunu atzīmi</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Skolēns</th>
                <th>Priekšmets</th>
                <th>Atzīme</th>
                <th>Datums</th>
                <th>Darbības</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grades as $grade): ?>
            <tr>
                <td><?= htmlspecialchars($grade['first_name'].' '.$grade['last_name']) ?></td>
                <td><?= htmlspecialchars($grade['subject_name']) ?></td>
                <td><?= $grade['grade'] ?></td>
                <td><?= date('d.m.Y', strtotime($grade['date'])) ?></td>
                <td>
                    <a href="edit_grade.php?id=<?= $grade['ID'] ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $grade['ID'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" 
                                onclick="return confirm('Vai tiešām vēlaties dzēst šo atzīmi?')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>