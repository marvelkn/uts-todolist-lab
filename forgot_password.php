<?php
session_start();
require_once 'includes/db_connect.php'; // Ensure this path is correct
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials (Consider moving these to a separate configuration file)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uts_lab_webpro";

// Establish database connection if not already connected
if (!isset($connect)) {
    $connect = new mysqli($servername, $username, $password, $dbname);
    if ($connect->connect_error) {
        die("Connection failed: " . $connect->connect_error);
    }
}

$recoveryStatus = ''; // Variable to store recovery status messages

if (isset($_POST["recover"])) {
    $email = $connect->real_escape_string($_POST["email"]); // Sanitize input

    // Check if the email exists in the database
    $sql = mysqli_query($connect, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($sql) <= 0) {
        $recoveryStatus = 'no_account';
    } else {
        $otp = rand(100000, 999999);
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Store OTP and expiry time in the database
        $update_query = "UPDATE users SET reset_token='$otp', reset_expires_at='$expires_at' WHERE email='$email'";
        $update_result = mysqli_query($connect, $update_query);

        if ($update_result) {
            // Send OTP via PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->SMTPDebug = 0; // Set to 2 for detailed debug output
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'marvel.kevin@student.umn.ac.id'; // Replace with your SMTP username
                $mail->Password = 'marvel1010'; // Replace with your SMTP password or App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('marvel.kevin@student.umn.ac.id', 'Password Recovery');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = "Your Password Recovery OTP";
                $mail->Body    = "<h3>Your OTP code is <strong>$otp</strong>. It will expire in 15 minutes.</h3>";

                $mail->send();

                // Set session variables
                $_SESSION['otp'] = $otp;
                $_SESSION['email'] = $email;

                $recoveryStatus = 'otp_sent';
            } catch (Exception $e) {
                error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                $recoveryStatus = 'mail_error';
            }
        } else {
            $recoveryStatus = 'update_error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - TaskMaster</title>
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

        .recovery-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            backdrop-filter: blur(4px);
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
            margin: 1rem;
        }

        .recovery-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .recovery-header h1 {
            color: #2c3e50;
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .recovery-header p {
            color: var(--secondary-color);
            font-size: 1.1rem;
            max-width: 80%;
            margin: 0 auto;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-floating input {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 1rem;
            height: calc(3rem + 2px);
        }

        .form-floating label {
            padding: 1rem;
        }

        .btn-recover {
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

        .btn-recover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            background-color: #3858c5;
        }

        .back-to-login {
            color: var(--secondary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .back-to-login:hover {
            color: var(--primary-color);
            transform: translateX(-5px);
        }

        .back-to-login i {
            margin-right: 0.5rem;
        }

        .recovery-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--secondary-color);
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
    </style>
</head>
<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape" style="top: 20%; left: 10%; background: #4e73df33; width: 60px; height: 60px; border-radius: 30% 70% 70% 30%/30% 30% 70% 70%;"></div>
        <div class="shape" style="top: 60%; left: 80%; background: #1cc88a33; width: 80px; height: 80px; border-radius: 50%;"></div>
        <div class="shape" style="top: 80%; left: 30%; background: #36b9cc33; width: 70px; height: 70px; border-radius: 50% 20% 30% 70%;"></div>
    </div>

    <div class="recovery-container">
        <a href="login.php" class="back-to-login">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>

        <div class="recovery-header">
            <h1>Password Recovery</h1>
            <p>Enter your email address and we'll send you an OTP to reset your password</p>
        </div>

        <form method="POST" action="">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email_address" name="email" placeholder="Enter your email" required>
                <label for="email_address"><i class="fas fa-envelope me-2"></i>Email Address</label>
            </div>

            <button type="submit" name="recover" class="btn btn-recover">
                <i class="fas fa-paper-plane me-2"></i> Send Recovery OTP
            </button>
        </form>

        <div class="recovery-footer">
            <p>Remember your password? <a href="login.php" class="text-primary">Login here</a></p>
        </div>
    </div>

    <script>
        <?php if ($recoveryStatus): ?>
            <?php if ($recoveryStatus === 'no_account'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Account Not Found',
                    text: 'Sorry, no account with that email exists.',
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#4e73df'
                });
            <?php elseif ($recoveryStatus === 'mail_error'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Email Error',
                    text: 'Failed to send OTP. Please try again later.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4e73df'
                });
            <?php elseif ($recoveryStatus === 'update_error'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Database Error',
                    text: 'Failed to update reset token. Please try again.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4e73df'
                });
            <?php elseif ($recoveryStatus === 'otp_sent'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'OTP Sent!',
                    text: 'An OTP has been sent to your email address.',
                    confirmButtonText: 'Continue',
                    confirmButtonColor: '#1cc88a'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'reset_password.php';
                    }
                });
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>
