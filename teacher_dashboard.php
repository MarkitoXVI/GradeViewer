<?php
include 'config.php';

// Check if teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Get teacher's information
$stmt = $conn->prepare("SELECT * FROM Teachers WHERE ID = ?");
$stmt->execute([$_SESSION['user_id']]);
$teacher = $stmt->fetch();

// Get counts for dashboard
$student_count = $conn->query("SELECT COUNT(*) FROM Students")->fetchColumn();
$subject_count = $conn->query("SELECT COUNT(*) FROM Subjects")->fetchColumn();
$grade_count = $conn->query("SELECT COUNT(*) FROM Grades")->fetchColumn();

include 'header.php';
?>

<div class="container mt-4">
    <!-- Teacher Profile Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                        <img src="<?= $teacher['avatar'] ? 'uploads/'.$teacher['avatar'] : 'default.jpg' ?>" 
     class="img-thumbnail" 
     alt="Profile Picture"
     style="width: 50px; height: 50px; object-fit: cover;">
                        </div>
                        <div class="col-md-10">
                            <h2>Welcome, <?= htmlspecialchars($teacher['first_name'].' '.$teacher['last_name']) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Students</h5>
                    <h2><?= $student_count ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Subjects</h5>
                    <h2><?= $subject_count ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Grades</h5>
                    <h2><?= $grade_count ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Sections -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-white bg-primary">
                    Student Management
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <a href="manage_students.php" class="list-group-item list-group-item-action">
                            View All Students
                        </a>
                        <a href="add_student.php" class="list-group-item list-group-item-action">
                            Add New Student
                        </a>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header text-white bg-success">
                    Subject Management
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <a href="manage_subjects.php" class="list-group-item list-group-item-action">
                            View All Subjects
                        </a>
                        <a href="add_subject.php" class="list-group-item list-group-item-action">
                            Add New Subject
                        </a>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header text-white bg-info">
                    Grade Management
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <a href="manage_grades.php" class="list-group-item list-group-item-action">
                            View All Grades
                        </a>
                        <a href="add_grade.php" class="list-group-item list-group-item-action">
                            Add New Grade
                        </a>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>