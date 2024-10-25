<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$list_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verify that the list belongs to the user
$stmt = $pdo->prepare("SELECT * FROM todo_lists WHERE id = ? AND user_id = ?");
$stmt->execute([$list_id, $user_id]);
$list = $stmt->fetch();

if (!$list) {
    redirect('dashboard.php');
}

// Handle adding new task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $task_name = sanitize_input($_POST['task_name']);
    if (!empty($task_name)) {
        if (add_task($pdo, $list_id, $task_name)) {
            $_SESSION['success_message'] = "Task added successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to add the task. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "Task name cannot be empty.";
    }
    redirect("view_list.php?id=$list_id");
}

// Handle toggling task completion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_task'])) {
    $task_id = intval($_POST['task_id']);
    $is_completed = isset($_POST['is_completed']) ? 1 : 0;

    if (toggle_task_completion($pdo, $task_id, $is_completed)) {
        $_SESSION['success_message'] = "Task updated successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to update the task. Please try again.";
    }
    redirect("view_list.php?id=$list_id");
}

// Handle task filtering
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Get tasks for the list based on the filter
if ($filter == 'completed') {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE list_id = ? AND is_completed = 1");
} elseif ($filter == 'incomplete') {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE list_id = ? AND is_completed = 0");
} else {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE list_id = ?");
}
$stmt->execute([$list_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- SweetAlert2 Handling for Session Messages -->
<?php if (isset($_SESSION['success_message'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo $_SESSION['success_message']; ?>',
            confirmButtonText: 'OK'
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
            confirmButtonText: 'OK'
        });
    </script>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($list['title']); ?> - Todo List</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center"><?php echo htmlspecialchars($list['title']); ?></h1>
        <nav class="my-3">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </nav>

        <!-- Task Filter Section -->
        <section id="filter-tasks" class="mb-5">
            <h2>Filter Tasks</h2>
            <a href="?id=<?php echo $list_id; ?>&filter=all" class="btn btn-outline-primary">All Tasks</a>
            <a href="?id=<?php echo $list_id; ?>&filter=completed" class="btn btn-outline-success">Completed Tasks</a>
            <a href="?id=<?php echo $list_id; ?>&filter=incomplete" class="btn btn-outline-warning">Incomplete Tasks</a>
        </section>

        <!-- Add New Task Section -->
        <section id="add-task">
            <h2>Add New Task</h2>
            <form method="post" class="input-group mb-3">
                <input type="text" name="task_name" class="form-control" placeholder="Enter task name" required>
                <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
            </form>
        </section>

        <!-- Task List Display Section -->
        <section id="task-list">
            <h2>Tasks</h2>
            <?php if (empty($tasks)): ?>
                <div class="alert alert-info">No tasks found.</div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($tasks as $task): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <input type="hidden" name="toggle_task" value="1">
                                <input type="checkbox" name="is_completed" 
                                       <?php echo $task['is_completed'] ? 'checked' : ''; ?> 
                                       onchange="this.form.submit()">
                                <span style="text-decoration: <?php echo $task['is_completed'] ? 'line-through' : 'none'; ?>">
                                    <?php echo htmlspecialchars($task['task_name']); ?>
                                </span>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>