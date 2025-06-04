<?php
include 'config.php';


// Pārbauda lietotāju
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Iegūstam skolotāja datus
$stmt = $conn->prepare("SELECT * FROM Teachers WHERE ID = ?");
$stmt->execute([$_SESSION['user_id']]);
$teacher = $stmt->fetch();

$error = '';
$success = '';

// Apstrādājam formas iesniegšanu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sagatavojam mainīgos
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $avatar = $teacher['avatar']; // Esošais avatars
        
        // Apstrādājam paroli
        $password_update = '';
        $params = [
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':id' => $_SESSION['user_id']
        ];
        
        if (!empty($_POST['new_password'])) {
            if (strlen($_POST['new_password']) < 8) {
                throw new Exception("Parolei jābūt vismaz 8 simbolus garai");
            }
            $password_update = ", password = :password";
            $params[':password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }
        
        // Apstrādājam avataru
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Pārbaudam faila tipu
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['avatar']['type'], $allowedTypes)) {
                throw new Exception("Atļauti tikai JPG, PNG vai GIF attēli");
            }
            
            // Ģenerējam unikālu faila nosaukumu
            $fileExt = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $fileName = 'avatar_'.$_SESSION['user_id'].'_'.time().'.'.$fileExt;
            $targetPath = $uploadDir.$fileName;
            
            // Pārvietojam failu
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                // Dzēšam veco avataru
                if (!empty($teacher['avatar']) && file_exists($uploadDir.$teacher['avatar'])) {
                    unlink($uploadDir.$teacher['avatar']);
                }
                $avatar = $fileName;
            } else {
                throw new Exception("Neizdevās augšupielādēt attēlu");
            }
        }
        
        $params[':avatar'] = $avatar;
        
        // Veicam atjaunināšanu datubāzē
        $sql = "UPDATE Teachers SET 
                first_name = :first_name,
                last_name = :last_name,
                avatar = :avatar
                $password_update
                WHERE ID = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        $success = "Profila iestatījumi veiksmīgi atjaunināti!";
        
        // Atjauninam skolotāja datus
        $stmt = $conn->prepare("SELECT * FROM Teachers WHERE ID = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $teacher = $stmt->fetch();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include 'header.php';

?>

<style>
    .settings-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .profile-avatar {
        width: 180px;
        height: 180px;
        object-fit: cover;
        border: 3px solid #7f7fd5;
        transition: all 0.3s ease;
    }
    
    .profile-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(127, 127, 213, 0.3);
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
    
    .btn-save {
        background: #7f7fd5;
        color: white;
        padding: 12px 30px;
        border-radius: 10px;
        transition: all 0.3s ease;
        border: none;
    }
    
    .btn-save:hover {
        background: #6c6cbd;
        transform: translateY(-2px);
    }
    
    .file-upload {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }
    
    .file-upload-input {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        cursor: pointer;
    }
    
    .file-upload-label {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        padding: 10px 20px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .file-upload-label:hover {
        border-color: #7f7fd5;
        background: #f3f4ff;
    }
</style>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="settings-card p-4">
            <div class="text-center mb-4">
                    <h3 class="mb-3" style="color: #2c3e50;">Profile Settings</h3>
                
                      
                
                <form method="POST" enctype="multipart/form-data" id="profileForm">
                    <!-- Avatar Section -->
                    <div class="text-center mb-4">
                        <div class="avatar-container position-relative d-inline-block">
                            <img src="<?= !empty($teacher['avatar']) ? 'uploads/'.$teacher['avatar'] : 'default.jpg' ?>" 
                                 id="avatarPreview"
                                 class="profile-avatar rounded-circle" 
                                 style="width: 180px; height: 180px; object-fit: cover;"
                                 alt="Profile Avatar">
                            <div class="mt-3">
                                <div class="file-upload">
                                    <input type="file" name="avatar" id="avatarInput" 
                                           class="file-upload-input" accept="image/*">
                                    <label for="avatarInput" class="file-upload-label">
                                        <i class="bi bi-camera me-2"></i>Change Photo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" id="profileForm">
                    <input type="hidden" name="form_submitted" value="1">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2" style="color: #4a5568;">First Name</label>
                        <input type="text" 
                               name="first_name" 
                               class="form-control form-control-custom" 
                               value="<?= htmlspecialchars($teacher['first_name']) ?>" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2" style="color: #4a5568;">Last Name</label>
                        <input type="text" 
                               name="last_name" 
                               class="form-control form-control-custom" 
                               value="<?= htmlspecialchars($teacher['last_name']) ?>" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2" style="color: #4a5568;">New Password</label>
                        <input type="password" 
                               name="new_password" 
                               class="form-control form-control-custom" 
                               placeholder="Enter new password">
                        <small class="text-muted mt-1 d-block">Minimum 8 characters</small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <button type="submit" class="btn btn-save" id="submitBtn">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
                    
                   

<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    avatarPreview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>