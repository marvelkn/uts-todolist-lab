<?php
session_start();
require_once 'includes/db_connect.php'; // Ensure this path is correct and points to your db connection file

// Ensure the $connect variable is defined for mysqli
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uts_lab_webpro";

// Check if the connection is already established
if (!isset($connect)) {
    $connect = new mysqli($servername, $username, $password, $dbname);
    if ($connect->connect_error) {
        die("Connection failed: " . $connect->connect_error);
    }
}

?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="Favicon.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Reset Password</title>
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

        /* Additional Styles for Reset Password */
        .recovery-container .form-group {
            position: relative;
        }

        .form-group label {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--secondary-color);
        }

        .form-group .form-control {
            padding-left: 45px;
        }

        .form-group .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: none;
        }

        .form-group .fa-lock,
        .form-group .fa-key {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }

        @media (max-width: 576px) {
            .recovery-container {
                padding: 1.5rem;
            }
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
            <h1>Password Reset</h1>
            <p>Enter the OTP sent to your email and set a new password</p>
        </div>

        <?php if(isset($_SESSION['email'])): ?>
        <form method="POST" action="">
            <div class="form-group mb-4">
                <i class="fas fa-key"></i>
                <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter OTP" required autofocus>
            </div>

            <div class="form-group mb-4">
                <i class="fas fa-lock"></i>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter New Password" required>
            </div>

            <button type="submit" name="reset_password" class="btn-recover">
                Reset Password
            </button>
        </form>
        <?php else: ?>
        <div class="alert alert-danger">
            Session expired. Please <a href="forgot_password.php">request a new OTP</a>.
        </div>
        <?php endif; ?>

        <div class="recovery-footer">
            <p>Remember your password? <a href="login.php" class="text-primary">Login here</a></p>
        </div>
    </div>

    <!-- Add debug output - remove in production -->
    <script>
        console.log("Session OTP: <?php echo isset($_SESSION['otp']) ? $_SESSION['otp'] : 'not set'; ?>");
        console.log("Session Email: <?php echo isset($_SESSION['email']) ? $_SESSION['email'] : 'not set'; ?>");
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($resetStatus): ?>
            <?php if ($resetStatus === 'success'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Password Reset Successful',
                    text: 'Your password has been reset successfully.',
                    confirmButtonText: 'Login',
                    confirmButtonColor: '#1cc88a',
                    allowOutsideClick: false
                }).then((result) => {
                    window.location.href = 'login.php';
                });
            <?php elseif ($resetStatus === 'invalid_otp'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid OTP',
                    text: 'The OTP you entered is invalid or has expired.',
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#4e73df'
                });
            <?php elseif ($resetStatus === 'update_error'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Update Error',
                    text: 'There was an error updating your password. Please try again.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4e73df'
                });
            <?php elseif ($resetStatus === 'no_session'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Session Error',
                    text: 'Your session has expired. Please initiate the password reset process again.',
                    confirmButtonText: 'Go to Forgot Password',
                    confirmButtonColor: '#4e73df',
                    allowOutsideClick: false
                }).then((result) => {
                    window.location.href = 'forgot_password.php';
                });
            <?php elseif ($resetStatus === 'weak_password'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must be at least 8 characters long.',
                    confirmButtonText: 'Try Again',
                    confirmButtonColor: '#4e73df'
                });
            <?php endif; ?>
        <?php endif; ?>
    });
    </script>

</body>
</html>


<?php
if (isset($_POST['reset_password'])) {
    $otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    
    // Only log if session variables exist
    if (isset($_SESSION['otp']) && isset($_SESSION['email'])) {
        error_log("Submitted OTP: " . $otp);
        error_log("Session OTP: " . $_SESSION['otp']);
        error_log("Session Email: " . $_SESSION['email']);
    }

    // Check if session variables exist
    if (!isset($_SESSION['otp']) || !isset($_SESSION['email'])) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Session Expired',
                text: 'Please request a new OTP.',
                confirmButtonText: 'OK'
            }).then((result) => {
                window.location.href = 'forgot_password.php';
            });
        </script>";
    }
    // Verify OTP
    else if ($_SESSION['otp'] != $otp) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Invalid OTP',
                text: 'The OTP you entered is incorrect.',
                confirmButtonText: 'Try Again'
            });
        </script>";
    }
    else {
        // OTP is correct, proceed with password update
        $email = $_SESSION['email'];
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
        $result = mysqli_query($connect, $sql);
        
        if ($result) {
            // Clear session variables first
            $email = $_SESSION['email']; // Store email before clearing session
            unset($_SESSION['otp']);
            unset($_SESSION['email']);
            session_destroy();
            
            // Then show success message and redirect
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Password has been reset successfully!',
                    confirmButtonText: 'Login Now',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php';
                    } else {
                        window.location.href = 'login.php';
                    }
                });
            </script>";
            exit(); // Stop further execution
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update password. Please try again.',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
    }
}

?>
