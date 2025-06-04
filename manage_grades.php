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
    $_SESSION['success'] = "Grade deleted succesfully!";
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

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(120deg, #f0f2f5, #e6e9ef, #dde1e8);
            min-height: 100vh;
        }
        .management-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 30px;
        }
        .table-custom {
            border-radius: 12px;
            overflow: hidden;
        }
        .table-custom thead th {
            background: #7f7fd5;
            color: white;
            border: none;
            padding: 15px 20px;
        }
        .table-custom tbody td {
            vertical-align: middle;
            padding: 12px 20px;
            border-color: #f0f2f5;
        }
        .table-custom tbody tr:hover {
            background-color: rgba(127, 127, 213, 0.05);
        }
        .btn-add {
            background: #7f7fd5;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .btn-add:hover {
            background: #6c6cbd;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(127, 127, 213, 0.3);
        }
        .action-btn {
            width: 35px;
            height: 35px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }
        .btn-edit {
            background: rgba(127, 127, 213, 0.1);
            color: #7f7fd5;
        }
        .btn-edit:hover {
            background: #7f7fd5;
            color: white;
        }
        .btn-delete {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        .btn-delete:hover {
            background: #dc3545;
            color: white;
        }
        .filter-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        .form-select-custom {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1.5px solid #d1d9e6;
        }
        .btn-filter {
            background: #7f7fd5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
        }
        .btn-filter:hover {
            background: #6c6cbd;
            color: white;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="management-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="teacher_dashboard.php" class="btn btn-back">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </a>
                <h2 class="mb-0" style="color: #2c3e50;">Grade Management</h2>
            </div>
            <a href="add_grade.php" class="btn btn-add text-white">
                <i class="bi bi-plus-lg me-2"></i>Add Grade
            </a>
        </div>

        <?php if($success): ?>
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $success ?>
            </div>
        <?php endif; ?>

        <!-- Filtrēšanas forma -->
        <div class="filter-card mb-4">
            <form class="row g-3">
                <div class="col-md-4">
                    <select class="form-select form-select-custom" name="student">
                        <option value="">All students</option>
                        <?php foreach ($students as $student): ?>
                        <option value="<?= $student['ID'] ?>" <?= ($student_filter == $student['ID']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select form-select-custom" name="subject">
                        <option value="">All subjects</option>
                        <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['ID'] ?>" <?= ($subject_filter == $subject['ID']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subject['subject_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-custom" name="order">
                        <option value="desc" <?= ($order == 'DESC') ? 'selected' : '' ?>>Newest first</option>
                        <option value="asc" <?= ($order == 'ASC') ? 'selected' : '' ?>>Oldest first</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-filter w-100">
                        <i class="bi bi-funnel me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <?php if(empty($grades)): ?>
            <div class="alert alert-info d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2"></i>
                No grades found. Add your first grade!
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Grade</th>
                            <th>Date</th>
                            <th>Actions</th>
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
                                <div class="d-flex gap-2">
                                    <a href="edit_grade.php?id=<?= $grade['ID'] ?>" 
                                       class="btn btn-edit action-btn"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="delete_id" value="<?= $grade['ID'] ?>">
                                        <button type="submit" 
                                                class="btn btn-delete action-btn"
                                                onclick="return confirm('Are you sure you want to delete this grade?')"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>