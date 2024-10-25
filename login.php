<?php
session_start();
require_once 'includes/db_connect.php'; // Ensure this path is correct

// Define database connection variables if needed
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = trim($_POST['email']); // Can be email or username
    $password = $_POST['password'];
    $error = '';

    // Determine if the identifier is email or username
    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $stmt = $connect->prepare("SELECT * FROM users WHERE email = ?");
    } else {
        $stmt = $connect->prepare("SELECT * FROM users WHERE username = ?");
    }

    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $_SESSION['error_message'] = "Email/Username is not registered. Please check or register a new account.";
        header("Location: login.php");
        exit();
    }

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['success_message'] = "Welcome, {$user['username']}!";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Invalid password. Please try again.";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TaskMaster</title>
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

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            backdrop-filter: blur(4px);
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
            margin: 1rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #2c3e50;
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--secondary-color);
        }

        .form-floating {
            margin-bottom: 1rem;
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

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            background-color: #3858c5;
        }

        .back-to-home {
            color: var(--secondary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .back-to-home:hover {
            color: var(--primary-color);
            transform: translateX(-5px);
        }

        .back-to-home i {
            margin-right: 0.5rem;
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-footer a:hover {
            color: #3858c5;
            text-decoration: underline;
        }

        .input-group-text {
            background: transparent;
            border-left: none;
            cursor: pointer;
        }

        .password-input {
            border-right: none;
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

    <div class="login-container">
        <a href="index.php" class="back-to-home">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <div class="login-header">
            <h1>Welcome Back!</h1>
            <p>Please login to your account</p>
        </div>

        <form method="post" action="">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="email" name="email" placeholder="Email/Username" required>
                <label for="email"><i class="fas fa-user me-2"></i>Email/Username</label>
            </div>

            <div class="form-floating">
                <div class="input-group">
                    <input type="password" class="form-control password-input" id="password" name="password" required>
                    <span class="input-group-text" onclick="togglePassword()">
                        <i class="fas fa-eye" id="togglePassword"></i>
                    </span>
                </div>
                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
        </form>

        <div class="login-footer">
            <p class="mb-2">
                <a href="forgot_password.php">Forgot your password?</a>
            </p>
            <p>
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');
            
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
    </script>

    <!-- SweetAlert2 Messages -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: '<?php echo $_SESSION['error_message']; ?>',
                confirmButtonText: 'Try Again',
                confirmButtonColor: '#4e73df'
            });
        </script>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Login Successful!',
                text: '<?php echo $_SESSION['success_message']; ?>',
                confirmButtonText: 'Go to Dashboard',
                confirmButtonColor: '#1cc88a'
            }).then(() => {
                window.location.href = 'dashboard.php';
            });
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
</body>
</html>