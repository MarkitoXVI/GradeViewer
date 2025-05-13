<?php
include 'config.php';
if ($_SESSION['role'] !== 'teacher') header("Location: index.php");

// Dzēšana
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM Students WHERE ID = ?");
    $stmt->execute([$_POST['delete_id']]);
}

// Studentu saraksts
$stmt = $conn->query("SELECT * FROM Students");
$students = $stmt->fetchAll();
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
            <a href="add_student.php" class="btn btn-add text-white">
                <i class="bi bi-plus-lg me-2"></i>Add Student
            </a>
        </div>

        <style>
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
        </style>
        

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
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Username</th>
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