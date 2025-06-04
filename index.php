<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #7f7fd5, #86a8e7, #91eae4);
            min-height: 100vh;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            body {
    background: linear-gradient(135deg, #667eea, #764ba2);
    min-height: 100vh;
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 40px 30px;
    width: 100%;
    max-width: 400px;
    position: relative;
}

.form-control {
    border-radius: 12px;
    padding: 14px 18px;
    border: 1px solid #ccc;
    font-size: 16px;
    width: 100%;
    transition: all 0.3s ease-in-out;
    margin-bottom: 20px;
}

.form-control:focus {
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.3);
    border-color: #667eea;
    outline: none;
}

.login-btn {
    background: linear-gradient(120deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 14px;
    font-size: 16px;
    font-weight: bold;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.login-btn:hover {
    background: linear-gradient(120deg, #5a6dd0, #693c9e);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.input-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
    font-size: 18px;
    pointer-events: none;
}

    </style>
</head>
<body class="d-flex align-items-center">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="login-card p-5">
                <div class="text-center mb-4">
                    <img src="https://cdn-icons-png.flaticon.com/512/668/668709.png" 
                         alt="Login Icon" 
                         style="width: 80px; height: 80px;">
                    <h2 class="mt-3 mb-2" style="color: #2c3e50;">Skolas Portāls</h2>
                    <p class="text-muted">Lūdzu, pierakstieties savā kontā</p>
                </div>

                <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger">Nepareizs lietotājvārds vai parole!</div>
                <?php endif; ?>

                <form action="login.php" method="post">
                    <div class="mb-3 position-relative">
                        <input type="text" 
                               name="username" 
                               class="form-control" 
                               placeholder="Lietotājvārds"
                               required>
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                        </svg>
                    </div>
                    
                    <div class="mb-4 position-relative">
                        <input type="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Parole"
                               required>
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                        </svg>
                    </div>

                    <button type="submit" class="btn btn-primary login-btn">
                        <span class="fw-bold">PIERAKSTĪTIES</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>