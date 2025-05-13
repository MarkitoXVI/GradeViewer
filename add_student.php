<?php
include 'config.php';
if ($_SESSION['role'] !== 'teacher') header("Location: index.php");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        ':first_name' => sanitizeInput($_POST['first_name']),
        ':last_name' => sanitizeInput($_POST['last_name']),
        ':username' => sanitizeInput($_POST['username']),
        ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
    ];

    try {
        $stmt = $conn->prepare("INSERT INTO Students (first_name, last_name, username, password) 
                              VALUES (:first_name, :last_name, :username, :password)");
        $stmt->execute($data);
        header("Location: manage_students.php");
    } catch(PDOException $e) {
        $error = "Lietotājvārds jau eksistē!";
    }
}
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
            padding: 2rem;
        }
        .form-control-custom {
            border-radius: 10px;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control-custom:focus {
            border-color: #7f7fd5;
            box-shadow: 0 0 0 3px rgba(127, 127, 213, 0.25);
        }
        .btn-primary-custom {
            background: #7f7fd5;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .btn-primary-custom:hover {
            background: #6c6cbd;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(127, 127, 213, 0.3);
        }
    </style>
</head>
<body class="d-flex align-items-center">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="management-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0" style="color: #2c3e50;">Add a new student</h2>
                    <a href="teacher_dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </a>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center mb-4">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <div><?= $error ?></div>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Name</label>
                        <input type="text" 
                               name="first_name" 
                               class="form-control form-control-custom" 
                               required
                               placeholder="Sandis">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Last name</label>
                        <input type="text" 
                               name="last_name" 
                               class="form-control form-control-custom" 
                               required
                               placeholder="Iesmiņš">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" 
                               name="username" 
                               class="form-control form-control-custom" 
                               required
                               placeholder="sandis.iesmins">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" 
                               name="password" 
                               class="form-control form-control-custom" 
                               required
                               placeholder="********">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary-custom text-white">
                            <i class="bi bi-save me-2"></i>Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>