<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Check if the user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $list_title = sanitize_input($_POST['list_title']);
    $error = '';

    if (empty($list_title)) {
        $error = "List title cannot be empty.";
    } else {
        if (create_todo_list($pdo, $user_id, $list_title)) {
            // Success message using SweetAlert2
            $_SESSION['success_message'] = "Your to-do list \"$list_title\" has been created successfully.";
            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            $error = "Failed to create the list. Please try again.";
        }
    }

    // Show error message using SweetAlert2 if there’s any error
    if (!empty($error)) {
        $_SESSION['error_message'] = $error;
        header("Location: dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New To-Do List - Todo List</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Create New To-Do List</h2>
        <form method="post" action="" class="mt-4">
            <div class="mb-3">
                <label for="list_title" class="form-label">List Title:</label>
                <input type="text" id="list_title" name="list_title" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Create List</button>
        </form>
        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <!-- SweetAlert2 for Success or Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'List Created!',
                text: '<?php echo $_SESSION['success_message']; ?>',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'dashboard.php';
            });
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $_SESSION['error_message']; ?>',
                confirmButtonText: 'Try Again'
            }).then(() => {
                window.location.href = 'dashboard.php';
            });
        </script>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
</body>
</html>