<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require __DIR__ . '/vendor/autoload.php';
require_once 'vendor/phpmailer/phpmailer/src/SMTP.php';
require_once 'vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';
$user_id = $_SESSION['user_id'];

error_log('Starting profile update process');

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle main profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Update username
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$username, $user_id]);

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_picture']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);

            if (!in_array(strtolower($filetype), $allowed)) {
                throw new Exception("Invalid file type. Allowed types: " . implode(', ', $allowed));
            }

            $new_filename = uniqid() . '.' . $filetype;
            $upload_dir = 'uploads/';

            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Full path for the new file
            $upload_path = $upload_dir . $new_filename;

            // Move uploaded file
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                throw new Exception("Failed to upload profile picture.");
            }

            // Delete old profile picture if exists
            if (!empty($user['foto'])) {
                $old_file = $upload_dir . $user['foto'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            // Update database with new filename
            $stmt = $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?");
            $stmt->execute([$new_filename, $user_id]);
        }

        // Commit transaction
        $pdo->commit();
        $success = "Profile updated successfully!";

        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Todo List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .profile-picture-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1rem;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .profile-picture-upload {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: #007bff;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .profile-picture-upload:hover {
            background: #0056b3;
            transform: scale(1.1);
        }

        .profile-picture-upload i {
            font-size: 14px;
            /* Adjust size of the plus icon */
        }

        .profile-picture-upload input[type="file"] {
            display: none;
        }


        .profile-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .profile-section h3 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .btn-custom {
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-buttons {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 1rem;
        }

        .input-group-text {
            background-color: transparent;
            border-right: none;
            padding: 0.5rem 0.75rem;
            display: flex;
            align-items: center;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            text-align: center;
            white-space: nowrap;
            border: 1px solid #ced4da;
            border-radius: 0.25rem 0 0 0.25rem;
            height: calc(1.5em + 0.75rem + 2px);
            /* Match input height */
        }

        .input-group .form-control {
            border-left: none;
            height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
        }

        .input-group-text+.form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .input-group .form-control:focus {
            border-color: #ced4da;
            box-shadow: none;
        }

        /* Fix focus state colors */
        .input-group:focus-within .input-group-text {
            border-color: #80bdff;
        }

        .input-group:focus-within .form-control {
            border-color: #80bdff;
        }

        /* Profile Picture Container */
        .profile-picture-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1rem;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }



        .form-control {
            border-left: none;
            padding-left: 0;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
    </style>
</head>

<body>
    <div class="nav-buttons">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-custom">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
        <a href="#" id="logoutBtn" class="btn btn-danger btn-custom">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </div>

    <div class="container profile-container">
        <div class="profile-header">
            <div class="profile-picture-container">
                <img src="<?php echo $user['foto'] ? 'uploads/' . htmlspecialchars($user['foto']) : 'https://via.placeholder.com/150'; ?>"
                    alt="Profile Picture" class="profile-picture" id="preview-image">
                <label for="profile_picture" class="profile-picture-upload">
                    <i class="fas fa-camera"></i>
                </label>
            </div>
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <form method="post" action="" enctype="multipart/form-data" id="profile-form">
            <div class="profile-section">
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*" style="display: none;">

                <div class="profile-section">
                    <h3><i class="fas fa-user me-2"></i>Basic Information</h3>
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" id="username" name="username"
                                value="<?php echo htmlspecialchars($user['username']); ?>" class="form-control"
                                required>
                        </div>
                    </div>
                    <input type="hidden" name="update_profile" value="1">
                    <button type="submit" class="btn btn-primary btn-custom w-100">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </div>
        </form>

        <div class="profile-section">
            <h3><i class="fas fa-envelope me-2"></i>Email Settings</h3>
            <form method="post" action="verification.php" id="emailForm">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter new email">
                    </div>
                </div>
                <button type="button" id="changeEmailBtn" class="btn btn-warning btn-custom w-100">
                    <i class="fas fa-pen me-2"></i>Change Email
                </button>
            </form>
        </div>


        <div class="profile-section">
            <h3><i class="fas fa-lock me-2"></i>Security</h3>
            <form action="verify_password.php" method="post" id="form1">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                            class="form-control" readonly>
                    </div>
                </div>
                <button type="button" id="changePasswordBtn" class="btn btn-warning btn-custom w-100">
                    <i class="fas fa-key me-2"></i>Change Password
                </button>
            </form>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?php echo $success; ?>',
                confirmButtonText: 'OK'
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $error; ?>',
                confirmButtonText: 'OK'
            });
        </script>
    <?php endif; ?>

    <script>
        document.getElementById('logoutBtn').addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        });

        // Handle email change button
        document.getElementById('changeEmailBtn').addEventListener('click', function () {
            Swal.fire({
                title: 'Change Email',
                text: 'An OTP will be sent to your new email for verification.',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then(() => {
                document.getElementById('emailForm').submit();
            });
        });

        // Handle password change button
        document.getElementById('changePasswordBtn').addEventListener('click', function () {
            Swal.fire({
                title: 'Change Password',
                text: 'An OTP will be sent to your email for password verification.',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then(() => {
                document.getElementById('form1').submit();
            });
        });
    </script>
    <script>
        // Image preview functionality
        document.getElementById('profile_picture').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview-image').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Existing button handlers
        document.getElementById('logoutBtn').addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        });

        document.getElementById('changeEmailBtn').addEventListener('click', function () {
            Swal.fire({
                title: 'Change Email',
                text: 'An OTP will be sent to your new email for verification.',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then(() => {
                document.getElementById('emailForm').submit();
            });
        });

        document.getElementById('changePasswordBtn').addEventListener('click', function () {
            Swal.fire({
                title: 'Change Password',
                text: 'An OTP will be sent to your email for password verification.',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then(() => {
                document.getElementById('form1').submit();
            });
        });

        document.querySelector('.profile-picture-upload').addEventListener('click', function () {
            document.getElementById('profile_picture').click();
        });

        document.getElementById('profile_picture').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview-image').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Remove the old email change button handler and use this instead
        document.getElementById('emailForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const newEmail = document.getElementById('new_email').value.trim();

            if (!newEmail) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please enter a new email address.',
                });
                return;
            }

            Swal.fire({
                title: 'Change Email',
                text: 'An OTP will be sent to your new email for verification.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Send OTP',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        })

    </script>
</body>

</html>