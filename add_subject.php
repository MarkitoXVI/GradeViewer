<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name']);
    
    if (empty($subject_name)) {
        $error = "Subject's name can't be empty!";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO Subjects (subject_name) VALUES (?)");
            $stmt->execute([$subject_name]);
            $_SESSION['success'] = "Subject added successfully!";
            header("Location: manage_subjects.php");
            exit();
        } catch (PDOException $e) {
            $error = "Subject with this name already exists!";
        }
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
            background: linear-gradient(135deg, #f3f4f6, #e2e6ec);
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .management-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 12px 45px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            transition: all 0.4s ease;
        }

        .form-control-custom {
            border-radius: 12px;
            padding: 14px 22px;
            border: 1.5px solid #d1d9e6;
            font-size: 16px;
            transition: all 0.3s ease-in-out;
            width: 100%;
            margin-bottom: 20px;
            background-color: #fff;
        }

        .form-control-custom:focus {
            border-color: #667;
        }

        .btn-primary-custom {
            background: #7f7fd5;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary-custom:hover {
            background: #6c6cbd;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(127, 127, 213, 0.3);
        }

        .btn-secondary-custom {
            background: rgba(127, 127, 213, 0.1);
            color: #7f7fd5;
            border: 2px solid #7f7fd5;
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-secondary-custom:hover {
            background: #7f7fd5;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="d-flex align-items-center">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="management-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0" style="color: #2c3e50;">Add a new subject</h2>
                    <a href="teacher_dashboard.php" class="btn btn-secondary-custom">
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
                        <label class="form-label fw-bold">Subject name</label>
                        <input type="text" 
                               name="subject_name" 
                               class="form-control form-control-custom" 
                               required
                               maxlength="100"
                               placeholder="Geography">
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary-custom text-white flex-grow-1">
                            <i class="bi bi-save me-2"></i>Save
                        </button>
                        <a href="manage_subjects.php" class="btn btn-secondary-custom flex-grow-1">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>