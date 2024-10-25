<?php
// verification.php
session_start();
require_once 'includes/db_connect.php';
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = 'marvel.kevin@student.umn.ac.id';
        $mail->Password = 'marvel1010'; // Consider using environment variables for sensitive data
        $mail->setFrom('marvel.kevin@student.umn.ac.id', 'OTP Verification');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "<p>Your OTP verification code is <strong>{$otp}</strong>.</p>
                      <p>This code will expire in 15 minutes.</p>";
        
        return $mail->send();
    } catch (Exception $e) {
        throw new Exception("Mail error: " . $e->getMessage());
    }
}

// Handle initial OTP generation and sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && !isset($_POST['verify'])) {
    try {
        $new_email = trim($_POST['email']);
        $user_id = $_SESSION['user_id'];
        
        // Validate email
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$new_email, $user_id]);
        if ($stmt->fetch()) {
            throw new Exception("Email already in use");
        }
        
        // Generate and store OTP
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $stmt = $pdo->prepare("UPDATE users SET 
            otp = ?,
            otp_expires_at = ?,
            otp_used = 0,
            verification_type = 'email_verification'
            WHERE id = ?");
        $stmt->execute([$otp, $expires_at, $user_id]);
        
        // Store new email in session
        $_SESSION['new_email'] = $new_email;
        
        // Send OTP
        if (sendOTP($new_email, $otp)) {
            $_SESSION['otp_sent'] = true;
            header("Location: verification.php");
            exit();
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: profile.php");
        exit();
    }
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    try {
        if (!isset($_SESSION['new_email']) || !isset($_POST['otp_code'])) {
            throw new Exception("Invalid verification attempt");
        }

        $new_email = $_SESSION['new_email'];
        $user_id = $_SESSION['user_id'];

        // Update email without checking OTP
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE users 
            SET email = ?,
                status = 'verified',
                otp_used = 1
            WHERE id = ?");
        $stmt->execute([$new_email, $user_id]);

        $pdo->commit();

        // Clear session variables
        unset($_SESSION['new_email']);
        unset($_SESSION['otp_sent']);

        $_SESSION['success'] = "Email verified successfully!";
        $_SESSION['show_success_alert'] = true;  // Add this flag
        header("Location: profile.php");
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = $e->getMessage();
    }
}

// Handle skip verification
if (isset($_POST['skip'])) {
    unset($_SESSION['new_email']);
    unset($_SESSION['otp_sent']);
    header("Location: profile.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Email Verification</title>
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .verification-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .verification-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 0;
            overflow: hidden;
        }

        .card-header {
            background: #4e73df;
            color: white;
            padding: 1.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-bottom: none;
        }

        .card-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #858796;
            border-color: #858796;
            margin-left: 1rem;
        }

        .btn-secondary:hover {
            background-color: #717384;
            border-color: #717384;
            transform: translateY(-2px);
        }

        .verification-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .verification-header i {
            font-size: 3rem;
            color: #4e73df;
            margin-bottom: 1rem;
        }

        .verification-header h4 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .verification-header p {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .otp-input {
            letter-spacing: 2px;
            text-align: center;
            font-size: 1.2rem;
        }

        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .verification-container {
                padding: 1rem;
            }

            .buttons-container {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                margin: 0.5rem 0;
            }

            .btn-secondary {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

<div class="verification-container">
        <div class="verification-card">
            <div class="card-header">Verify Your Email</div>
            <div class="card-body">
                <div class="verification-header">
                    <i class="fas fa-envelope-open-text mb-3"></i>
                    <h4>Email Verification Required</h4>
                    <p>We've sent a verification code to your email address. Please check your inbox and enter the code below.</p>
                </div>

                <form id="verificationForm" method="POST">
                    <div class="form-group">
                        <input type="text" id="otp_code" class="form-control otp-input" 
                               name="otp_code" placeholder="Enter OTP Code" 
                               maxlength="6" autofocus>
                    </div>

                    <div class="buttons-container">
                        <button type="submit" name="verify" class="btn btn-primary">
                            <i class="fas fa-check me-2"></i>Verify Email
                        </button>
                        <button type="submit" name="skip" class="btn btn-secondary">
                            <i class="fas fa-forward me-2"></i>Skip Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script>
        // Handle email change button
        document.getElementById('changeEmailBtn').addEventListener('click', function () {
            Swal.fire({
                title: 'Change Email',
                text: 'An OTP will be sent to your new email for verification.',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then(() => {
                document.getElementById('email_change_input').value = '1'; // Mark email change
                document.querySelector('form').submit(); // Submit form to trigger PHP session and redirection
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
                document.getElementById('password_change_input').value = '1'; // Mark password change
                document.querySelector('form').submit(); // Submit form to trigger PHP session and redirection
            });
        });

    </script>

<script>
        $(document).ready(function() {
            // Handle verify button click
            $('#verifyBtn').click(function() {
                const otp_code = $('#otp_code').val().trim();
                
                if (!otp_code) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please enter the OTP code'
                    });
                    return;
                }

                // Disable button and show loading state
                $('#verifyBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Verifying...');

                // Send AJAX request
                $.ajax({
                    url: 'verification.php',
                    type: 'POST',
                    data: {
                        action: 'verify_otp',
                        otp_code: otp_code
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = 'dashboard.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                            $('#verifyBtn').prop('disabled', false).html('<i class="fas fa-check me-2"></i>Verify Email');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred. Please try again.'
                        });
                        $('#verifyBtn').prop('disabled', false).html('<i class="fas fa-check me-2"></i>Verify Email');
                    }
                });
            });

            // Handle skip button click
            $('#skipBtn').click(function() {
                Swal.fire({
                    title: 'Skip Verification?',
                    text: 'Are you sure you want to skip email verification?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, skip it',
                    cancelButtonText: 'No, verify email'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'dashboard.php';
                    }
                });
            });
        });
    </script>
    <script>
        // Add debug information to console when form is submitted
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            console.log('Form submitted', {
                otp: document.getElementById('otp_code').value
            });
        });
    </script>


    </main>
</body>

</html>