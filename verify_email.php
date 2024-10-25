<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require __DIR__ . '/vendor/autoload.php'; // Autoload PHPMailer and other dependencies
require_once 'vendor/phpmailer/phpmailer/connect/connection.php'; // Adjust this path to where your database connection is located
require_once 'vendor/phpmailer/phpmailer/src/SMTP.php'; 
require_once 'vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$error = '';


        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['mail'] = $_POST['email']; // Save new email to session

        // Send OTP via email
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = 'marvel.kevin@student.umn.ac.id'; // Replace with your email
        $mail->Password = 'marvel1010'; // Replace with your email password
        $mail->setFrom('marvel.kevin@student.umn.ac.id', 'Email Verification');
        $mail->addAddress($_POST['email']);
        $mail->isHTML(true);
        $mail->Subject = "Verify Your Email Address";
        $mail->Body = "<p>Your OTP verification code is <strong>$otp</strong>.</p>";

        if ($mail->send()) {
            header("Location: verification.php"); // Redirect to verification page
            exit();
        } else {
            $error = "Error sending verification email.";
        }
    
?>

<!-- OTP Verification Form -->
<form method="POST" action="">
    <label for="otp">Enter OTP sent to your new email:</label>
    <input type="text" name="otp" required>
    <button type="submit">Verify</button>
</form>
<p><?php echo $error; ?></p>
