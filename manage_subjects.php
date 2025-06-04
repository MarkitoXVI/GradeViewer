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

// Filtrācijas parametri
$search = $_GET['search'] ?? '';
$order = $_GET['order'] ?? 'asc';

// Iegūstam priekšmetus ar filtrāciju
$query = "SELECT * FROM Subjects WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND subject_name LIKE ?";
    $params[] = "%$search%";
}

// Pievienojam kārtošanu
$order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';
$query .= " ORDER BY subject_name $order";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$subjects = $stmt->fetchAll();

// Ziņu izvade
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
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
        .sort-btn {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .sort-btn:hover {
            color: #e6e6e6;
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
                <h2 class="mb-0" style="color: #2c3e50;">Subject Management</h2>
            </div>
            <a href="add_subject.php" class="btn btn-add text-white">
                <i class="bi bi-plus-lg me-2"></i>Add Subject
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $success ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Filtrēšanas forma -->
        <div class="filter-card mb-4">
            <form class="row g-3">
                <div class="col-md-8">
                    <input type="text" 
                           name="search" 
                           class="form-control form-control-custom" 
                           placeholder="Search by subject name"
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <select name="order" class="form-select form-select-custom">
                        <option value="asc" <?= $order === 'asc' ? 'selected' : '' ?>>A-Z</option>
                        <option value="desc" <?= $order === 'desc' ? 'selected' : '' ?>>Z-A</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-filter w-100">
                        <i class="bi bi-funnel me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <?php if(empty($subjects)): ?>
            <div class="alert alert-info d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2"></i>
                No subjects found. Add your first subject!
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>
                                <a href="?search=<?= urlencode($search) ?>&order=<?= $order === 'asc' ? 'desc' : 'asc' ?>" class="sort-btn">
                                    Subject Name
                                    <i class="bi bi-arrow-<?= $order === 'asc' ? 'down' : 'up' ?> ms-2"></i>
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?= htmlspecialchars($subject['ID']) ?></td>
                            <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="edit_subject.php?id=<?= $subject['ID'] ?>" 
                                       class="btn btn-edit action-btn"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="delete_id" value="<?= $subject['ID'] ?>">
                                        <button type="submit" 
                                                class="btn btn-delete action-btn"
                                                onclick="return confirm('Are you sure you want to delete this subject?')"
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