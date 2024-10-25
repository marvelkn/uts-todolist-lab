<?php
session_start();
session_destroy(); // Destroy session data

// Redirect to login page immediately
header("Location: index.php");
exit();