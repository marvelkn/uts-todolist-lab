<?php
session_start();

// Include the database connection
require_once 'includes/db_connect.php'; // Update this path to the correct location of your connection script

if (!isset($_SESSION['mail'])) {
    header("Location: register.php"); // Redirect if the user hasn't registered yet
    exit();
}

$error = '';

if (isset($_POST['verify'])) {
    $otp_code = $_POST['otp_code'];
    
    // Compare the OTP entered by the user with the one stored in the session
    if ($otp_code == $_SESSION['otp']) {
        // OTP is correct, verify the user's email in the database
        $email = $_SESSION['mail'];
        $stmt = $pdo->prepare("UPDATE users SET status = 'verified' WHERE email = ?");
        $stmt->execute([$email]);

        // Unset OTP session variables after successful verification
        unset($_SESSION['otp']);
        unset($_SESSION['mail']);

        echo "<script>alert('Email verification successful. You can now log in.'); window.location.replace('login.php');</script>";
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - TaskMaster</title>
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
        }

        .form-floating input {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 1rem;
            height: calc(3rem + 2px);
            text-align: center;
            font-size: 1.2rem;
            letter-spacing: 0.5rem;
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

        .verify-footer {
            text-align: center;
            margin-top: 2rem;
        }

        .verify-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .verify-footer a:hover {
            color: #3858c5;
            text-decoration: underline;
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

        .email-info {
            background-color: rgba(78, 115, 223, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .email-info p {
            margin: 0;
            color: var(--primary-color);
            font-weight: 500;
        }

        .otp-input-group {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
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
        <a href="login.php" class="back-to-login">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>

        <div class="verify-header">
            <i class="fas fa-envelope-circle-check verify-icon"></i>
            <h1>Verify Your Account</h1>
            <p>Enter the verification code we just sent to your email</p>
        </div>

        <div class="email-info">
            <p><i class="fas fa-envelope me-2"></i><?php echo isset($_SESSION['mail']) ? $_SESSION['mail'] : ''; ?></p>
        </div>

        <form method="POST">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="otp_code" name="otp_code" 
                       placeholder="Enter OTP" maxlength="6" required 
                       pattern="[0-9]{6}" title="Please enter a 6-digit code">
                <label for="otp_code"><i class="fas fa-key me-2"></i>Verification Code</label>
            </div>

            <button type="submit" name="verify" class="btn btn-verify">
                <i class="fas fa-check-circle me-2"></i>Verify Account
            </button>
        </form>

        <div class="verify-footer">
            <p>Didn't receive the code? <a href="#" onclick="resendOTP()">Resend Code</a></p>
        </div>
    </div>

    <script>
        function resendOTP() {
            // You can implement the resend OTP functionality here
            Swal.fire({
                icon: 'info',
                title: 'Resending Code',
                text: 'Please wait while we send you a new verification code.',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }

        // Add input validation for OTP
        document.getElementById('otp_code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });

        // Show success/error messages using SweetAlert
        <?php if(isset($_POST["verify"])): ?>
            <?php if($otp == $otp_code): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Your account has been verified successfully.',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = 'login.php';
                });
            <?php else: ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Code',
                    text: 'The verification code you entered is incorrect. Please try again.',
                });
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>
