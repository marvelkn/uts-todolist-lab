<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Check if the user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Process the deletion only if the request is POST and the list_id is provided
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['list_id'])) {
    $user_id = $_SESSION['user_id'];
    $list_id = intval($_POST['list_id']);

    // Verify that the to-do list belongs to the logged-in user
    $stmt = $pdo->prepare("SELECT * FROM todo_lists WHERE id = ? AND user_id = ?");
    $stmt->execute([$list_id, $user_id]);
    $list = $stmt->fetch();

    if ($list) {
        // Attempt to delete the to-do list
        if (delete_todo_list($pdo, $list_id, $user_id)) {
            // Set success message
            $_SESSION['success_message'] = "To-do list deleted successfully.";
        } else {
            // Set error message if deletion fails
            $_SESSION['error_message'] = "Failed to delete the to-do list. Please try again.";
        }
    } else {
        // Set error message if the list doesn't belong to the user
        $_SESSION['error_message'] = "You don't have permission to delete this list.";
    }
}

// Redirect back to the dashboard and display the result using SweetAlert2
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleting To-Do List...</title>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        // Check for success or error message in the session
        <?php if (isset($_SESSION['success_message'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: '<?php echo $_SESSION['success_message']; ?>',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'dashboard.php'; // Redirect back to dashboard
            });
            <?php unset($_SESSION['success_message']); ?>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $_SESSION['error_message']; ?>',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'dashboard.php'; // Redirect back to dashboard
            });
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </script>
</body>
</html>