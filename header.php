<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="teacher_dashboard.php">Teacher Portal</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="manage_students.php">Students</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_subjects.php">Subjects</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_grades.php">Grades</a></li>
            </ul>
            <div class="d-flex">
                <a href="settings.php" class="btn btn-outline-light me-2">
                    <i class="bi bi-gear"></i> Settings
                </a>
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>
