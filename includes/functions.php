<?php
// Function to handle file uploads with SweetAlert2 feedback
function handle_file_upload($file, $allowed_types, $upload_dir) {
    if ($file['error'] == 0) {
        $filename = $file['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed_types)) {
            $new_filename = uniqid() . '.' . $filetype;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                return $new_filename;
            } else {
                display_error("Failed to move uploaded file.");
            }
        } else {
            display_error("Invalid file type. Allowed types: " . implode(', ', $allowed_types));
        }
    } else {
        display_error("File upload error. Please try again.");
    }
    return false;
}

// Helper function to display errors using SweetAlert2
function display_error($message) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '$message',
            confirmButtonText: 'OK'
        }).then(() => {
            window.history.back();
        });
    </script>
    ";
    exit();
}

// Function to check if the user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to redirect to a specified page
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to sanitize input data
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to create a to-do list in the database
function create_todo_list($pdo, $user_id, $list_title) {
    $stmt = $pdo->prepare("INSERT INTO todo_lists (user_id, title, status) VALUES (?, ?, 'incomplete')");
    return $stmt->execute([$user_id, $list_title]);
}

// Function to delete a to-do list from the database
function delete_todo_list($pdo, $list_id, $user_id) {
    $stmt = $pdo->prepare("DELETE FROM todo_lists WHERE id = ? AND user_id = ?");
    return $stmt->execute([$list_id, $user_id]);
}

// Function to add a task to a list
function add_task($pdo, $list_id, $task_name) {
    $stmt = $pdo->prepare("INSERT INTO tasks (list_id, task_name, is_completed) VALUES (?, ?, 0)");
    return $stmt->execute([$list_id, $task_name]);
}

// Function to toggle task completion
function toggle_task_completion($pdo, $task_id, $is_completed) {
    $stmt = $pdo->prepare("UPDATE tasks SET is_completed = ? WHERE id = ?");
    return $stmt->execute([$is_completed, $task_id]);
}

// Function to get tasks for a specific list
function get_tasks_for_list($pdo, $list_id) {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE list_id = ?");
    $stmt->execute([$list_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>