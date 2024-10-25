<?php 
session_start();
require __DIR__ . '/vendor/autoload.php'; // Autoload PHPMailer and other dependencies
require_once 'vendor/phpmailer/phpmailer/connect/connection.php'; // Adjust this path to where your database connection is located

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';

if (isset($_POST["register"])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $foto = null;

    // Check if user already exists
    $check_query = mysqli_query($connect, "SELECT * FROM users WHERE email ='$email'");
    $rowCount = mysqli_num_rows($check_query);

    if (!empty($email) && !empty($password) && !empty($username) && !empty($name)) {
        if ($rowCount > 0) {
            echo "<script>alert('User with email already exists!');</script>";
        } else {
            // Handle profile picture upload
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
                $filename = $_FILES["profile_picture"]["name"];
                $filetype = $_FILES["profile_picture"]["type"];
                $filesize = $_FILES["profile_picture"]["size"];

                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!array_key_exists($ext, $allowed)) {
                    $error = "Error: Please select a valid file format.";
                } else {
                    $maxsize = 5 * 1024 * 1024;
                    if ($filesize > $maxsize) {
                        $error = "Error: File size is larger than the allowed limit.";
                    } else {
                        if (in_array($filetype, $allowed)) {
                            $newFilename = uniqid() . "." . $ext;
                            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], "uploads/" . $newFilename)) {
                                $foto = $newFilename;
                            } else {
                                $error = "Error: There was a problem uploading your file. Please try again.";
                            }
                        } else {
                            $error = "Error: There was a problem uploading your file. Please try again.";
                        }
                    }
                }
            } else {
                $foto = 'default.jpg';
            }

            // If no error, insert new user
            if (empty($error)) {
                $result = mysqli_query($connect, "INSERT INTO users (username, name, email, password, foto, role, status) VALUES ('$username', '$name', '$email', '$password_hash', '$foto', 'user', 'unverified')");

                if ($result) {
                    // Generate OTP
                    $otp = rand(100000, 999999);
                    $_SESSION['otp'] = $otp;
                    $_SESSION['mail'] = $email;

                    // Send OTP email using PHPMailer
                    $mail = new PHPMailer;

                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->Port = 587;
                        $mail->SMTPAuth = true;
                        $mail->SMTPSecure = 'tls';

                        $mail->Username = 'marvel.kevin@student.umn.ac.id'; // Replace with your email
                        $mail->Password = 'marvel1010'; // Replace with your email password

                        $mail->setFrom('marvel.kevin@student.umn.ac.id', 'OTP Verification');
                        $mail->addAddress($email);

                        $mail->isHTML(true);
                        $mail->Subject = "Your verification code";
                        $mail->Body = "<p>Dear user, </p> <h3>Your OTP verification code is $otp</h3><br><br><p>Regards,</p><b>To-Do List Application</b>";

                        if (!$mail->send()) {
                            echo "<script>alert('Register Failed, Invalid Email.');</script>";
                        } else {
                            echo "<script>alert('Register Successful, OTP sent to $email'); window.location.replace('verify_register.php');</script>";
                        }
                    } catch (Exception $e) {
                        echo "<script>alert('Mailer Error: {$mail->ErrorInfo}');</script>";
                    }
                } else {
                    echo "<script>alert('Registration failed. Please try again.');</script>";
                }
            } else {
                echo "<script>alert('$error');</script>";
            }
        }
    }
}
?>


<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
<!------ Include the above in your HEAD tag ---------->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TaskMaster</title>
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

        .register-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            backdrop-filter: blur(4px);
            padding: 2.5rem;
            width: 100%;
            max-width: 500px;
            margin: 2rem;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h1 {
            color: #2c3e50;
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .register-header p {
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

        .file-upload {
            position: relative;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px dashed #e0e0e0;
            border-radius: 10px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload:hover {
            border-color: var(--primary-color);
        }

        .file-upload input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload i {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .file-upload-text {
            color: var(--secondary-color);
            margin: 0;
        }

        .btn-register {
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

        .btn-register:hover {
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

        .register-footer {
            text-align: center;
            margin-top: 2rem;
        }

        .register-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-footer a:hover {
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

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--secondary-color);
            z-index: 10;
        }

        .error-message {
            background-color: #ff5b5b;
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
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

    <div class="register-container">
        <a href="index.php" class="back-to-home">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <div class="register-header">
            <h1>Create Account</h1>
            <p>Join us and start managing your tasks</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required>
                <label for="name"><i class="fas fa-user me-2"></i>Full Name</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                <label for="username"><i class="fas fa-at me-2"></i>Username</label>
            </div>

            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
            </div>

            <div class="form-floating mb-3" style="position: relative;">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
            </div>

            <div class="file-upload">
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                <i class="fas fa-cloud-upload-alt"></i>
                <p class="file-upload-text">Click to upload profile picture</p>
            </div>

            <button type="submit" name="register" class="btn btn-register">
                <i class="fas fa-user-plus me-2"></i>Create Account
            </button>
        </form>

        <div class="register-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle');
            
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

        // Display selected file name
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            document.querySelector('.file-upload-text').textContent = fileName || 'Click to upload profile picture';
        });
    </script>
</body>
</html>