<?php
include 'config.php';
if ($_SESSION['role'] !== 'teacher') header("Location: index.php");

// Delete student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM Students WHERE ID = ?");
    $stmt->execute([$_POST['delete_id']]);
    $_SESSION['success'] = "Student deleted successfully!";
    header("Location: manage_students.php");
    exit();
}

// Export to CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_with_grades.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'First Name', 'Last Name', 'Username', 'Average Grade']);
    
    $query = "SELECT s.ID, s.first_name, s.last_name, s.username, 
              AVG(g.grade) as average_grade
              FROM Students s
              LEFT JOIN Grades g ON s.ID = g.student_id
              GROUP BY s.ID";
    $stmt = $conn->query($query);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['ID'],
            $row['first_name'],
            $row['last_name'],
            $row['username'],
            number_format($row['average_grade'], 2)
        ]);
    }
    fclose($output);
    exit();
}

// Filtering and sorting
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'id';
$order = $_GET['order'] ?? 'asc';

// Validate sorting parameters
$allowed_sorts = ['id', 'first_name', 'last_name', 'username', 'average_grade'];
$sort = in_array($sort, $allowed_sorts) ? $sort : 'id';
$order = $order === 'desc' ? 'DESC' : 'ASC';

// Get student list with average grades
$query = "SELECT s.ID, s.first_name, s.last_name, s.username, 
          AVG(g.grade) as average_grade
          FROM Students s
          LEFT JOIN Grades g ON s.ID = g.student_id
          WHERE 1=1";

$params = [];
if (!empty($search)) {
    $query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.username LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

$query .= " GROUP BY s.ID ORDER BY $sort $order";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Success message
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
        .btn-export {
            background: #28a745;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            transition: all 0.3s;
            color: white;
        }
        .btn-export:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            color: white;
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
        .btn-back {
            background: rgba(127, 127, 213, 0.1);
            color: #7f7fd5;
            border: 2px solid #7f7fd5;
            border-radius: 8px;
            padding: 8px 20px;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: #7f7fd5;
            color: white;
            transform: translateY(-2px);
        }
        .filter-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        .form-control-custom {
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
        .sort-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .sort-link:hover {
            color: #e6e6e6;
        }
        .average-grade {
            font-weight: 600;
            color: #2c3e50;
        }
    </style>
</head>
<body class="d-flex align-items-center">
<div class="container">
    <div class="management-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="teacher_dashboard.php" class="btn btn-back">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </a>
                <h2 class="mb-0" style="color: #2c3e50;">Student Management</h2>
            </div>
            <div class="d-flex gap-2">
                <a href="add_student.php" class="btn btn-add text-white">
                    <i class="bi bi-plus-lg me-2"></i>Add Student
                </a>
                <a href="?export=1" class="btn btn-export">
                    <i class="bi bi-download me-2"></i>Export
                </a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $success ?>
            </div>
        <?php endif; ?>

        <!-- Filter panel -->
        <div class="filter-card">
            <form class="row g-3">
                <div class="col-md-8">
                    <input type="text" 
                           name="search" 
                           class="form-control form-control-custom" 
                           placeholder="Search by name, last name or username"
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select form-select-custom">
                        <option value="first_name" <?= $sort === 'first_name' ? 'selected' : '' ?>>Sort by First Name</option>
                        <option value="last_name" <?= $sort === 'last_name' ? 'selected' : '' ?>>Sort by Last Name</option>
                        <option value="username" <?= $sort === 'username' ? 'selected' : '' ?>>Sort by Username</option>
                        <option value="average_grade" <?= $sort === 'average_grade' ? 'selected' : '' ?>>Sort by Grade</option>
                        <option value="id" <?= $sort === 'id' ? 'selected' : '' ?>>Sort by ID</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-filter w-100">
                        <i class="bi bi-funnel me-2"></i>Apply
                    </button>
                </div>
                <input type="hidden" name="order" value="<?= $order === 'DESC' ? 'desc' : 'asc' ?>">
            </form>
        </div>

        <?php if(empty($students)): ?>
            <div class="alert alert-info d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2"></i>
                No students found. Add your first student!
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>
                                <a href="?search=<?= urlencode($search) ?>&sort=id&order=<?= $sort === 'id' && $order === 'ASC' ? 'desc' : 'asc' ?>" class="sort-link">
                                    ID <?= $sort === 'id' ? ($order === 'ASC' ? '<i class="bi bi-arrow-up ms-2"></i>' : '<i class="bi bi-arrow-down ms-2"></i>') : '' ?>
                                </a>
                            </th>
                            <th>
                                <a href="?search=<?= urlencode($search) ?>&sort=first_name&order=<?= $sort === 'first_name' && $order === 'ASC' ? 'desc' : 'asc' ?>" class="sort-link">
                                    First Name <?= $sort === 'first_name' ? ($order === 'ASC' ? '<i class="bi bi-arrow-up ms-2"></i>' : '<i class="bi bi-arrow-down ms-2"></i>') : '' ?>
                                </a>
                            </th>
                            <th>
                                <a href="?search=<?= urlencode($search) ?>&sort=last_name&order=<?= $sort === 'last_name' && $order === 'ASC' ? 'desc' : 'asc' ?>" class="sort-link">
                                    Last Name <?= $sort === 'last_name' ? ($order === 'ASC' ? '<i class="bi bi-arrow-up ms-2"></i>' : '<i class="bi bi-arrow-down ms-2"></i>') : '' ?>
                                </a>
                            </th>
                            <th>
                                <a href="?search=<?= urlencode($search) ?>&sort=username&order=<?= $sort === 'username' && $order === 'ASC' ? 'desc' : 'asc' ?>" class="sort-link">
                                    Username <?= $sort === 'username' ? ($order === 'ASC' ? '<i class="bi bi-arrow-up ms-2"></i>' : '<i class="bi bi-arrow-down ms-2"></i>') : '' ?>
                                </a>
                            </th>
                            <th>
                                <a href="?search=<?= urlencode($search) ?>&sort=average_grade&order=<?= $sort === 'average_grade' && $order === 'ASC' ? 'desc' : 'asc' ?>" class="sort-link">
                                    Avg. Grade <?= $sort === 'average_grade' ? ($order === 'ASC' ? '<i class="bi bi-arrow-up ms-2"></i>' : '<i class="bi bi-arrow-down ms-2"></i>') : '' ?>
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['ID']) ?></td>
                            <td><?= htmlspecialchars($student['first_name']) ?></td>
                            <td><?= htmlspecialchars($student['last_name']) ?></td>
                            <td><?= htmlspecialchars($student['username']) ?></td>
                            <td class="average-grade">
                                <?= $student['average_grade'] ? number_format($student['average_grade'], 2) : 'N/A' ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="edit_student.php?id=<?= $student['ID'] ?>" 
                                       class="btn btn-edit action-btn"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="delete_id" value="<?= $student['ID'] ?>">
                                        <button type="submit" 
                                                class="btn btn-delete action-btn"
                                                onclick="return confirm('Are you sure you want to delete this student?')"
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