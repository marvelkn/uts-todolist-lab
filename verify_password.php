<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require __DIR__ . '/vendor/autoload.php';
require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';  
require_once 'vendor/phpmailer/phpmailer/src/SMTP.php'; 
require_once 'vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize error and success variables
$error = '';
$success = '';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle initial form submission for OTP generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['otp'])) {
    // Generate OTP
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    
    // Get user's email
    $email = $user['email'];

    // Send OTP via email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        
        $mail->Username = 'marvel.kevin@student.umn.ac.id';
        $mail->Password = 'marvel1010';
        
        $mail->setFrom('marvel.kevin@student.umn.ac.id', 'OTP Verification');
        $mail->addAddress($email);
        
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "<p>Your OTP verification code is <strong>$otp</strong>.</p>";

        if ($mail->send()) {
            $success = "OTP has been sent to your email.";
        } else {
            $error = "Error sending OTP email: " . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        $error = "Error sending OTP: " . $e->getMessage();
        error_log("PHPMailer Error: " . $e->getMessage());
    }
}

// Handle OTP verification and password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $input_otp = trim($_POST['otp']);
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);

    if (empty($input_otp) || empty($old_password) || empty($new_password)) {
        $error = "All fields are required.";
    } elseif (!isset($_SESSION['otp'])) {
        $error = "OTP session has expired. Please request a new OTP.";
    } else {
        if ($input_otp == $_SESSION['otp']) {
            if (password_verify($old_password, $user['password'])) {
                try {
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$new_hashed_password, $user_id]);

                    // Clear session variables
                    unset($_SESSION['otp']);
                    $_SESSION['password_updated'] = true; // Add this flag
                    $success = "Your password has been updated successfully.";
                } catch (PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            } else {
                $error = "Old password is incorrect.";
            }
        } else {
            $error = "Invalid OTP. Please check your email and try again.";
        }
    }
}

// Only redirect if password was successfully updated
if (isset($_SESSION['password_updated'])) {
    unset($_SESSION['password_updated']);
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Password - TaskMaster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .verify-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            backdrop-filter: blur(4px);
            padding: 2.5rem;
            width: 100%;
            max-width: 500px;
            margin: 2rem;
        }

        .verify-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .verify-header h1 {
            color: #2c3e50;
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .verify-header p {
            color: var(--secondary-color);
        }

        .verify-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .form-floating {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-floating input {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 1rem;
            height: calc(3rem + 2px);
            background-color: white;
        }

        .form-floating label {
            padding: 1rem;
        }

        .btn-verify {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            background-color: #3858c5;
        }

        .back-to-profile {
            color: var(--secondary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .back-to-profile:hover {
            color: var(--primary-color);
            transform: translateX(-5px);
        }

        .floating-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            opacity: 0.5;
            animation: float 15s infinite;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(100px, 100px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--secondary-color);
            z-index: 10;
        }

        .verification-card {
            background-color: rgba(78, 115, 223, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(78, 115, 223, 0.2);
        }

        .alert {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        .alert-success {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--success-color);
        }

        .alert-danger {
            background-color: rgba(231, 74, 59, 0.1);
            color: #e74a3b;
        }
    </style>
</head>
<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape" style="top: 20%; left: 10%; background: #4e73df33; width: 60px; height: 60px; border-radius: 30% 70% 70% 30%/30% 30% 70% 70%;"></div>
        <div class="shape" style="top: 60%; left: 80%; background: #1cc88a33; width: 80px; height: 80px; border-radius: 50%;"></div>
        <div class="shape" style="top: 80%; left: 30%; background: #36b9cc33; width: 70px; height: 70px; border-radius: 50% 20% 30% 70%;"></div>
    </div>

    <div class="verify-container">
        <a href="profile.php" class="back-to-profile">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>

        <div class="verify-header">
            <i class="fas fa-lock verify-icon"></i>
            <h1>Password Verification</h1>
            <p>Verify your identity to change password</p>
        </div>

        <?php if (!isset($_SESSION['otp'])): ?>
            <div class="verification-card">
                <form method="POST" action="">
                    <p class="text-center mb-4"><i class="fas fa-envelope me-2"></i>We'll send a verification code to your email</p>
                    <button type="submit" class="btn btn-verify">
                        <i class="fas fa-paper-plane me-2"></i>Send Verification Code
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="verification-card">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter OTP" required>
                        <label for="otp"><i class="fas fa-key me-2"></i>Verification Code</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Old Password" required>
                        <label for="old_password"><i class="fas fa-lock me-2"></i>Old Password</label>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('old_password')"></i>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password" required>
                        <label for="new_password"><i class="fas fa-key me-2"></i>New Password</label>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('new_password')"></i>
                    </div>

                    <button type="submit" class="btn btn-verify">
                        <i class="fas fa-check-circle me-2"></i>Verify and Update Password
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = passwordInput.nextElementSibling.nextElementSibling;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Initialize SweetAlert messages if there are any PHP messages
        <?php if (!empty($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo addslashes($error); ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>

        
    </script>
</body>
</html>