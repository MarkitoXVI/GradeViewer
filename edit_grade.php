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

        $stmt = $conn->prepare("UPDATE Grades SET student_id = ?, subject_id = ?, grade = ? WHERE ID = ?");
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

$students = $conn->query("SELECT * FROM Students")->fetchAll();
$subjects = $conn->query("SELECT * FROM Subjects")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Labot Atzīmi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
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
            border-color: #7f7fd5;
            box-shadow: 0 0 0 0.25rem rgba(127, 127, 213, 0.25);
        }

        .btn-primary-custom {
            background: #7f7fd5;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 16px;
            color: white;
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
            font-size: 16px;
        }

        .btn-secondary-custom:hover {
            background: #7f7fd5;
            color: white;
            transform: translateY(-2px);
        }

        .form-label {
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .alert {
            border-radius: 12px;
            padding: 16px 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="management-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0" style="color: #2c3e50; font-weight: 700;">Labot atzīmi</h2>
                    <a href="manage_grades.php" class="btn btn-secondary-custom">
                        <i class="bi bi-arrow-left me-2"></i>Atpakaļ
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
                        <label class="form-label">Skolēns</label>
                        <select name="student_id" class="form-control form-control-custom" required>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['ID'] ?>" <?= ($student['ID'] == $grade['student_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Priekšmets</label>
                        <select name="subject_id" class="form-control form-control-custom" required>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject['ID'] ?>" <?= ($subject['ID'] == $grade['subject_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subject['subject_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Atzīme</label>
                        <input type="number" name="grade" class="form-control form-control-custom" 
                               value="<?= $grade['grade'] ?>" min="1" max="10" step="1" required>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary-custom flex-grow-1">
                            <i class="bi bi-save me-2"></i>Saglabāt
                        </button>
                        <a href="manage_grades.php" class="btn btn-secondary-custom flex-grow-1">
                            <i class="bi bi-x-circle me-2"></i>Atcelt
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
