<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$search_results = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['query'])) {
    $search_query = sanitize_input($_GET['query']);
    if (!empty($search_query)) {
        $search_results = search_tasks($pdo, $user_id, $search_query);
        if (empty($search_results)) {
            $_SESSION['error_message'] = "No tasks found matching your search.";
        }
    } else {
        $_SESSION['error_message'] = "Search query cannot be empty.";
    }
}
?>

<!-- SweetAlert2 Handling for Session Messages -->
<?php if (isset($_SESSION['error_message'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'No Results',
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
    <title>Search Tasks - Todo List</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Search Tasks</h1>
        <nav class="my-3 text-center">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </nav>

        <section id="search-form" class="mb-5">
            <form method="get" class="input-group">
                <input type="text" name="query" class="form-control" placeholder="Enter search query"
                       value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>" required>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </section>

        <section id="search-results">
            <h2>Search Results</h2>
            <?php if (empty($search_results) && isset($_GET['query'])): ?>
                <div class="alert alert-warning">No tasks found matching your search.</div>
            <?php elseif (!empty($search_results)): ?>
                <ul class="list-group">
                    <?php foreach ($search_results as $task): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="view_list.php?id=<?php echo $task['list_id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($task['task_name']); ?>
                            </a>
                            <span class="badge bg-secondary">
                                List: <?php echo htmlspecialchars(get_list_title($pdo, $task['list_id'])); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>

    <script src="assets/js/search.js"></script>
</body>
</html>