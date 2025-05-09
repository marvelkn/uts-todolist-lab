<?php
session_start();
require_once 'includes/db_connect.php'; // Ensure this path is correct

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's profile
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit();
}

// Handle task completion (from checkmark button)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_list_id'])) {
    $list_id = intval($_POST['complete_list_id']);
    $stmt = $pdo->prepare("UPDATE todo_lists SET status = 'completed' WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$list_id, $user_id])) {
        $_SESSION['success_message'] = "To-do list marked as complete.";
    } else {
        $_SESSION['error_message'] = "Failed to mark the to-do list as complete.";
    }
    header("Location: dashboard.php");
    exit();
}

// Handle task deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_list_id'])) {
    $list_id = intval($_POST['delete_list_id']);
    $stmt = $pdo->prepare("DELETE FROM todo_lists WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$list_id, $user_id])) {
        $_SESSION['success_message'] = "To-do list deleted.";
    } else {
        $_SESSION['error_message'] = "Failed to delete the to-do list.";
    }
    header("Location: dashboard.php");
    exit();
}

// Handle task addition via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_name'])) {
    $task_name = $_POST['task_name'];
    $due_date = $_POST['due_date'];
    $location = isset($_POST['location']) ? $_POST['location'] : null;
    $is_priority = isset($_POST['is_priority']) ? intval($_POST['is_priority']) : 0; // Set priority to 0 if not checked

    // Check if required fields are filled
    if (empty($task_name) || empty($due_date)) {
        $_SESSION['error_message'] = "Task name and due date are required.";
    } else {
        // Insert task into the database
        $stmt = $pdo->prepare("INSERT INTO todo_lists (user_id, title, due_date, location, priority) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $task_name, $due_date, $location, $is_priority])) {
            $_SESSION['success_message'] = "Task added successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to add the task.";
        }
    }

    header("Location: dashboard.php");
    exit();
}

// Fetch tasks based on filter (completed, incomplete, or all)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'incomplete';
$query = "SELECT * FROM todo_lists WHERE user_id = ?";

if ($filter === 'completed') {
    $query .= " AND status = 'completed'";
} elseif ($filter === 'incomplete') {
    $query .= " AND status = 'incomplete'";
} elseif ($filter === 'overdue') {
    // Convert server time to Asia/Jakarta timezone for comparison
    $current_time = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    $current_time = $current_time->format('Y-m-d H:i:s');
    $query .= " AND status = 'incomplete' AND due_date < ?";
} elseif ($filter === 'today') {
    $current_date = date('Y-m-d');
    $query .= " AND DATE(due_date) = ?";
}

$stmt = $connect->prepare($query);

if ($filter === 'overdue') {
    $stmt->bind_param("is", $user_id, $current_time);
} elseif ($filter === 'today') {
    $stmt->bind_param("is", $user_id, $current_date);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$tasks = $stmt->get_result();

try {
    if ($filter === 'today') {
        $current_date = date('Y-m-d'); // Get today's date in 'Y-m-d' format
        $query = "SELECT * FROM todo_lists WHERE user_id = ? AND DATE(due_date) = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $current_date]);
    } else {
        // Handle other filters
        $query = "SELECT * FROM todo_lists WHERE user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
    }

    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching tasks: " . $e->getMessage();
}


// Handle task recovery (from the recover button)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recover_list_id'])) {
    $list_id = intval($_POST['recover_list_id']);
    $stmt = $pdo->prepare("UPDATE todo_lists SET status = 'incomplete' WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$list_id, $user_id])) {
        $_SESSION['success_message'] = "Task recovered successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to recover the task.";
    }
    header("Location: dashboard.php");
    exit();
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'incomplete'; // Default filter is 'incomplete'
$query = "SELECT * FROM todo_lists WHERE user_id = ?";

if ($filter === 'completed') {
    $query .= " AND status = 'completed'";
} elseif ($filter === 'incomplete') {
    $query .= " AND status = 'incomplete'";
} elseif ($filter === 'overdue') {
    $current_time = date('Y-m-d H:i:s');
    $query .= " AND status = 'incomplete' AND due_date < ?";
} elseif ($filter === 'today') {
    $current_date = date('Y-m-d');
    $query .= " AND DATE(due_date) = ?";
}

$stmt = $connect->prepare($query);

// Bind the correct parameters based on the filter
if ($filter === 'overdue') {
    $stmt->bind_param("is", $user_id, $current_time);
} elseif ($filter === 'today') {
    $stmt->bind_param("is", $user_id, $current_date);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$tasks = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Task Manager</title>
    <link crossorigin="anonymous" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            width: 250px;
            background-color: #fff;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar a {
            text-decoration: none;
            color: #333;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: background-color 0.2s;
        }

        .sidebar a:hover {
            background-color: #f0f0f0;
        }

        .sidebar a.active {
            background-color: #fce8e6;
            color: #db4c3f;
        }

        .sidebar i {
            width: 20px;
            margin-right: 10px;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .search-box {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
            margin: 10px 0;
        }

        .task-item {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .logout-btn {
            color: #dc3545 !important;
            margin-top: auto;
        }

        .priority-icon {
            color: #dc3545;
            margin-right: 10px;
        }

        /* Today section highlight */
        .sidebar a[href="?filter=today"] {
            background-color: #ffe8e6;
            color: #db4c3f;
        }

        /* Completed tasks style */
        .completed-task {
            opacity: 0.7;
            text-decoration: line-through;
        }

        /* Task buttons */
        .btn-task {
            padding: 5px 10px;
            margin-left: 5px;
            border-radius: 4px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        /* Remove the specific styling for Today section */
        .sidebar a[href="?filter=today"] {
            /* Remove the specific background color */
            background-color: initial;
            color: initial;
        }

        /* Update the active state styling */
        .sidebar a.active {
            background-color: #fce8e6;
            color: #db4c3f;
        }

        /* Hover state for non-active items */
        .sidebar a:not(.active):hover {
            background-color: #f0f0f0;
            color: #333;
        }

        .view-toggle .btn {
            padding: 0.5rem 1rem;
        }

        .view-toggle .btn.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        /* View Styles */
        /* List View */
        #tasksContainer.list-view .tasks-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        #tasksContainer.list-view .task-item {
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 1rem;
            height: auto;
        }

        #tasksContainer.list-view .task-content {
            flex-direction: row;
            align-items: center;
        }

        #tasksContainer.list-view .task-details {
            margin-left: auto;
            flex-direction: row;
            align-items: center;
        }

        /* Box/Grid View */
        #tasksContainer.box-view .tasks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        #tasksContainer.box-view .task-item {
            display: flex;
            flex-direction: column;
            height: 200px;
            padding: 1.5rem;
        }

        #tasksContainer.box-view .task-content {
            flex-direction: column;
        }

        #tasksContainer.box-view .task-details {
            flex-direction: column;
        }

        #tasksContainer.box-view .task-actions {
            margin-top: auto;
        }

        #tasksContainer.box-view .task-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        #tasksContainer.box-view .task-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        #tasksContainer.box-view .task-details {
            font-size: 0.9rem;
            color: #666;
        }

        #tasksContainer.box-view .task-actions {
            margin-top: auto;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        /* Common Task Styles */
        .task-item {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .task-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .task-content {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .priority-icon {
            color: #dc3545;
        }

        .task-details {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .sidebar {
            width: 250px;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: 60px;
            padding: 20px 10px;
        }

        .sidebar.collapsed .sidebar-toggle {
            justify-content: center;
        }

        .sidebar-toggle {
            cursor: pointer;
            padding: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .sidebar.collapsed .sidebar-toggle i {
            transform: rotate(180deg);
        }

        /* Hide text when sidebar is collapsed */
        .sidebar.collapsed span:not(.sidebar-toggle span) {
            display: none;
        }

        /* Center icons when sidebar is collapsed */
        .sidebar.collapsed a {
            justify-content: center;
            padding: 12px 0;
        }

        .sidebar.collapsed i {
            margin-right: 0;
        }

        /* Adjust content margin when sidebar is collapsed */
        .content {
            margin-left: 250px;
            transition: all 0.3s ease;
        }

        .content.expanded {
            margin-left: 60px;
        }

        /* Handle user profile section when collapsed */
        .sidebar.collapsed .d-flex.align-items-center.mb-4 {
            justify-content: center;
        }

        .sidebar.collapsed .d-flex.align-items-center.mb-4 span {
            display: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                height: 100vh;
            }

            .sidebar.show {
                left: 0;
            }

            .content {
                margin-left: 0;
            }

            .content.expanded {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: flex;
            }
        }

        /* Update hover tooltip for collapsed sidebar */
        .sidebar.collapsed a {
            position: relative;
        }

        .sidebar.collapsed a:hover::after {
            content: attr(data-title);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            margin-left: 10px;
            font-size: 14px;
            white-space: nowrap;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: 60px;
        }

        .sidebar.collapsed span,
        .sidebar.collapsed .user-profile span,
        .sidebar.collapsed .search-box {
            display: none;
        }

        .sidebar.collapsed a {
            justify-content: center;
            padding: 12px 5px;
        }

        .sidebar.collapsed a i {
            margin-right: 0;
            font-size: 1.2rem;
        }

        .sidebar.collapsed .user-profile {
            justify-content: center;
            padding: 10px 5px;
        }

        /* Content adjustments */
        .content {
            margin-left: 250px;
            transition: all 0.3s ease;
            padding: 20px;
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        .content.expanded {
            margin-left: 60px;
        }

        .task-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .task-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: #2c3e50;
        }

        .task-date {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .task-location {
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Improved header section */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px 0;
        }

        .content-header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: #2c3e50;
        }

        /* Task actions styling */
        .task-actions {
            display: flex;
            gap: 8px;
        }

        .task-actions button {
            padding: 6px 12px;
            border-radius: 6px;
        }

        /* Priority indicator */
        .priority-icon {
            color: #dc3545;
            margin-right: 10px;
            font-size: 1rem;
        }

        /* View toggle buttons styling */
        .view-toggle {
            background: white;
            padding: 5px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .view-toggle button {
            padding: 8px 15px;
        }

        .view-toggle button.active {
            background-color: #e9ecef;
        }

        /* Priority and completed badges */
        .priority-badge {
            background-color: #ff4444;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }

        .completed-badge {
            background-color: #00C851;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }

        /* Update completed task styling */
        .completed-task {
            background-color: #f8f9fa;
            opacity: 0.8;
        }

        .completed-task .task-title {
            text-decoration: none;
            color: #6c757d;
        }

        /* Common Task Styles */
        .task-item {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            padding: 1rem;
        }

        .task-content {
            display: flex;
            gap: 1rem;
            flex: 1;
        }

        .task-details {
            display: flex;
            gap: 1rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .tasks-grid {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* List View Styles */
        #tasksContainer.list-view .task-content {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        #tasksContainer.list-view .task-main {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        #tasksContainer.list-view .task-details {
            display: flex;
            gap: 2rem;
            margin-left: 2rem;
            /* Adds some space after the title */
        }

        /* Box View Styles */
        #tasksContainer.box-view .task-main {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        #tasksContainer.box-view .task-details {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        /* Common styles */
        .task-main {
            flex: 1;
        }

        .task-details {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </div>

            <div class="user-profile" style="cursor: pointer;" onclick="window.location.href='profile.php'">
                <img src="<?php echo $user['foto'] ? 'uploads/' . htmlspecialchars($user['foto']) : 'https://via.placeholder.com/40'; ?>"
                    alt="User avatar" class="rounded-circle" width="40" height="40">
                <span class="ms-2"><?php echo htmlspecialchars($user['name']); ?></span>
            </div>

            <a href="#" id="add-task-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Add task</span>
            </a>

            <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i>
                <span>All tasks</span>
            </a>

            <a href="?filter=today" class="<?php echo $filter === 'today' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-day"></i>
                <span>Today</span>
            </a>

            <a href="?filter=completed" class="<?php echo $filter === 'completed' ? 'active' : ''; ?>">
                <i class="fas fa-check"></i>
                <span>Completed</span>
            </a>

            <a href="?filter=incomplete" class="<?php echo $filter === 'incomplete' ? 'active' : ''; ?>">
                <i class="fas fa-times"></i>
                <span>Incomplete</span>
            </a>

            <a href="?filter=overdue" class="<?php echo $filter === 'overdue' ? 'active' : ''; ?>">
                <i class="fas fa-exclamation-circle"></i>
                <span>Overdue</span>
            </a>

            <div class="mb-3">
                <input type="text" id="searchTask" class="search-box" placeholder="Search tasks...">
            </div>


            <a href="#" id="aboutUsBtn" class="mt-auto">
                <i class="fas fa-info-circle"></i>
                <span>About Us</span>
            </a>

            <a href="logout.php" class="text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>


    <!-- Main Content -->
    <div class="content" id="mainContent">
        <div class="content-header">
            <h1><?php echo ucfirst($filter); ?> Tasks</h1>
            <div class="view-toggle">
                <button class="btn btn-outline-secondary me-2" id="listViewBtn">
                    <i class="fas fa-list"></i>
                </button>
                <button class="btn btn-outline-secondary" id="boxViewBtn">
                    <i class="fas fa-th-large"></i>
                </button>
            </div>
        </div>

        <!-- Tasks Container -->
        <div id="tasksContainer" class="list-view">
            <div class="tasks-grid">
                <?php while ($task = $tasks->fetch_assoc()): ?>
                    <div class="task-item <?php echo $task['status'] === 'completed' ? 'completed-task' : ''; ?>"
                        data-task-id="<?php echo $task['id']; ?>"
                        data-task-title="<?php echo htmlspecialchars($task['title']); ?>"
                        data-task-due="<?php echo htmlspecialchars($task['due_date']); ?>"
                        data-task-location="<?php echo htmlspecialchars($task['location']); ?>"
                        data-task-priority="<?php echo $task['priority']; ?>">

                        <div class="task-content">
                            <!-- Group title and badges -->
                            <div class="task-main">
                                <?php if ($task['priority'] == 1): ?>
                                    <span class="priority-badge">Priority</span>
                                <?php endif; ?>
                                <?php if ($task['status'] === 'completed' && $filter === 'all'): ?>
                                    <span class="completed-badge ms-2">Completed</span>
                                <?php endif; ?>
                                <div class="task-title ms-2">
                                    <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                                </div>

                                <!-- Task details moved here -->
                                <div class="task-details">
                                    <div class="task-date">
                                        <i class="far fa-calendar-alt me-2"></i>
                                        <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                    </div>
                                    <?php if (!empty($task['location'])): ?>
                                        <div class="task-location">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <?php echo htmlspecialchars($task['location']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php
                                    $due_date = new DateTime($task['due_date'], new DateTimeZone('Asia/Jakarta'));
                                    $current_time = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

                                    if ($task['status'] !== 'completed' && $due_date < $current_time): ?>
                                        <span class="badge mb-2 bg-danger ms-2">Overdue by
                                            <?php
                                            $interval = $current_time->diff($due_date);
                                            if ($interval->days > 0) {
                                                echo $interval->format('%d days');
                                            } else if ($interval->h > 0) {
                                                echo $interval->format('%h hours');
                                            } else {
                                                echo $interval->format('%i minutes');
                                            }
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="task-actions">
                            <?php if ($filter === 'all'): ?>
                                <?php if ($task['status'] !== 'completed'): ?>
                                    <form method="post" action="" class="d-inline">
                                        <input type="hidden" name="complete_list_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="" class="d-inline">
                                    <input type="hidden" name="delete_list_id" value="<?php echo $task['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php elseif ($filter === 'completed'): ?>
                                <form method="post" action="">
                                    <input type="hidden" name="recover_list_id" value="<?php echo $task['id']; ?>">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-redo"></i> Recover
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="" class="d-inline">
                                    <input type="hidden" name="complete_list_id" value="<?php echo $task['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form method="post" action="" class="d-inline">
                                    <input type="hidden" name="delete_list_id" value="<?php echo $task['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                const sidebarToggle = document.getElementById('sidebarToggle');

                // Load saved state
                const sidebarState = localStorage.getItem('sidebarState');
                if (sidebarState === 'collapsed') {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }

                // Add data-title attributes to all sidebar links for hover tooltips
                document.querySelectorAll('.sidebar a').forEach(link => {
                    const text = link.textContent.trim();
                    link.setAttribute('data-title', text);
                });

                sidebarToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');

                    // Save state
                    localStorage.setItem('sidebarState',
                        sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded'
                    );
                });

                // Handle responsive behavior
                function handleResize() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.add('mobile');
                        mainContent.style.marginLeft = '0';
                    } else {
                        sidebar.classList.remove('mobile');
                        if (!sidebar.classList.contains('collapsed')) {
                            mainContent.style.marginLeft = '250px';
                        }
                    }
                }

                // Initial check and event listener for window resize
                handleResize();
                window.addEventListener('resize', handleResize);

                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function (event) {
                    if (window.innerWidth <= 768 &&
                        !sidebar.contains(event.target) &&
                        !event.target.matches('.sidebar-toggle, .sidebar-toggle *')) {
                        sidebar.classList.remove('show');
                    }
                });
            });
        </script>

        <script>
            // Event listener for task clicks to show details using SweetAlert
            document.querySelectorAll('.task-item').forEach(function (taskElement) {
                taskElement.addEventListener('click', function () {
                    const taskTitle = this.getAttribute('data-task-title');
                    const taskDueDate = this.getAttribute('data-task-due');
                    const taskLocation = this.getAttribute('data-task-location');
                    const taskPriority = this.getAttribute('data-task-priority');

                    // Prepare the content for SweetAlert
                    const dueDateContent = taskDueDate ? `<p><strong>Due Date:</strong> ${taskDueDate}</p>` : '<p><strong>Due Date:</strong> No due date specified</p>';
                    const locationContent = taskLocation ? `<p><strong>Location:</strong> ${taskLocation}</p>` : '<p><strong>Location:</strong> No location specified</p>';
                    const priorityContent = taskPriority == 1 ? `<p><strong>Priority:</strong> Yes</p>` : '<p><strong>Priority:</strong> No</p>';

                    // Show SweetAlert with task details
                    Swal.fire({
                        title: `<strong>${taskTitle}</strong>`,
                        html: dueDateContent + locationContent + priorityContent,
                        icon: 'info',
                        confirmButtonText: 'Close'
                    });
                });
            });
        </script>


        <script>
            // Function to open the modal and display task details
            function showTaskDetails(task) {
                // Set modal content
                document.getElementById('taskTitle').textContent = task.title;
                document.getElementById('taskDueDate').textContent = task.due_date ? task.due_date : 'No due date';
                document.getElementById('taskLocation').textContent = task.location ? task.location : 'No location specified';
                document.getElementById('taskPriority').textContent = task.priority == 1 ? 'Yes' : 'No';

                // Show the modal
                var myModal = new bootstrap.Modal(document.getElementById('taskDetailsModal'), {
                    keyboard: false
                });
                myModal.show();
            }

            // Event listener for task clicks
            document.querySelectorAll('.task-item').forEach(function (taskElement) {
                taskElement.addEventListener('click', function () {
                    const taskId = this.getAttribute('data-task-id');
                    const taskTitle = this.getAttribute('data-task-title');
                    const taskDueDate = this.getAttribute('data-task-due');
                    const taskLocation = this.getAttribute('data-task-location');
                    const taskPriority = this.getAttribute('data-task-priority');

                    // Prepare task object to pass to modal
                    const task = {
                        title: taskTitle,
                        due_date: taskDueDate,
                        location: taskLocation,
                        priority: taskPriority
                    };

                    // Call the function to display task details
                    showTaskDetails(task);
                });
            });
        </script>

        <!-- SweetAlert2 for Success/Error Alerts -->
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

        <!-- Bootstrap JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

        <script>
            document.getElementById('add-task-btn').addEventListener('click', function () {
                Swal.fire({
                    title: 'Add New Task',
                    html: `
            <div class="mb-3">
                <input type="text" id="task-name" class="swal2-input" placeholder="Task Name">
            </div>
            <div class="mb-3">
                <label for="due-date" class="form-label" style="display: block; text-align: left; margin-left: 10px;">Due Date</label>
                <input type="date" id="due-date" class="swal2-input" style="margin-bottom: 0.5em;">
            </div>
            <div class="mb-3">
                <label for="due-time" class="form-label" style="display: block; text-align: left; margin-left: 10px;">Due Time</label>
                <input type="time" id="due-time" class="swal2-input" style="margin-bottom: 0.5em;">
            </div>
            <div class="mb-3">
                <input type="text" id="location" class="swal2-input" placeholder="Location (optional)">
            </div>
            <div class="form-check" style="margin: 1em;">
                <input type="checkbox" id="is-priority" class="form-check-input">
                <label class="form-check-label" for="is-priority">Priority Task</label>
            </div>
        `,
                    showCancelButton: true,
                    confirmButtonText: 'Add Task',
                    cancelButtonText: 'Cancel',
                    focusConfirm: false,
                    preConfirm: () => {
                        const taskName = document.getElementById('task-name').value;
                        const dueDate = document.getElementById('due-date').value;
                        const dueTime = document.getElementById('due-time').value;
                        const location = document.getElementById('location').value;
                        const isPriority = document.getElementById('is-priority').checked ? 1 : 0;

                        if (!taskName || !dueDate || !dueTime) {
                            Swal.showValidationMessage('Task name, date, and time are required');
                            return false;
                        }

                        // Combine date and time
                        const combinedDateTime = `${dueDate} ${dueTime}`;

                        // Send task to server via POST request
                        return fetch(window.location.href, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                task_name: taskName,
                                due_date: combinedDateTime,
                                location: location,
                                is_priority: isPriority
                            })
                        })
                            .then(response => response.text())
                            .then(() => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: 'Task added successfully!',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.reload();
                                });
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while adding your task.'
                                });
                            });
                    },
                    customClass: {
                        input: 'form-control'
                    }
                });
            });
        </script>
        <script>
            // Search functionality
            document.getElementById('searchTask').addEventListener('input', function (e) {
                const searchTerm = e.target.value.toLowerCase();
                document.querySelectorAll('.task-item').forEach(task => {
                    const taskTitle = task.getAttribute('data-task-title').toLowerCase();
                    task.style.display = taskTitle.includes(searchTerm) ? '' : 'none';
                });
            });

            // About Us Modal
            document.getElementById('aboutUsBtn').addEventListener('click', function () {
    Swal.fire({
        title: '<span style="color: #4e73df; font-size: 2.5rem; font-weight: 800;">Meet Our Team</span>',
        html: `
            <style>
                .developer-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                    gap: 25px;
                    padding: 20px;
                    max-width: 1200px;
                    margin: 0 auto;
                }

                .developer-card {
                    background: linear-gradient(145deg, #ffffff, #f5f7fa);
                    border-radius: 20px;
                    padding: 25px;
                    text-align: center;
                    transition: all 0.3s ease;
                    position: relative;
                    overflow: hidden;
                    box-shadow: 0 10px 20px rgba(78, 115, 223, 0.1);
                }

                .developer-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 15px 30px rgba(78, 115, 223, 0.2);
                }

                .developer-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 5px;
                    background: linear-gradient(90deg, #4e73df, #36b9cc);
                }

                .profile-image {
                    width: 150px;
                    height: 150px;
                    border-radius: 20px;
                    object-fit: cover;
                    margin-bottom: 20px;
                    border: 4px solid white;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                    transition: transform 0.3s ease;
                }

                .developer-card:hover .profile-image {
                    transform: scale(1.05);
                }

                .dev-name {
                    color: #2c3e50;
                    font-size: 1.4rem;
                    font-weight: 700;
                    margin: 10px 0 5px;
                }

                .dev-id {
                    color: #858796;
                    font-size: 1rem;
                    margin: 5px 0;
                }

                .dev-role {
                    color: #4e73df;
                    font-weight: 600;
                    font-size: 1.1rem;
                    margin: 10px 0;
                }

                .dev-specialty {
                    display: inline-block;
                    background: rgba(78, 115, 223, 0.1);
                    color: #4e73df;
                    padding: 5px 15px;
                    border-radius: 15px;
                    font-size: 0.9rem;
                    margin-top: 10px;
                }

                .social-links {
                    display: flex;
                    justify-content: center;
                    gap: 20px;
                    margin-top: 15px;
                }

                .social-link {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.3s ease;
                    text-decoration: none;
                }

                .social-link.github {
                    background: #333;
                    color: white;
                }

                .social-link.instagram {
                    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
                    color: white;
                }

                .social-link:hover {
                    transform: translateY(-3px);
                    filter: brightness(1.1);
                }

                .social-link i {
                    font-size: 1.2rem;
                }
            </style>
            <div class="developer-grid">
                <div class="developer-card" data-aos="fade-up">
                    <img src="src/marvel.jpeg" alt="Marvel Kevin Nathanael" class="profile-image">
                    <h3 class="dev-name">Marvel Kevin Nathanael</h3>
                    <p class="dev-id">00000108042</p>
                    <p class="dev-role">Lead Developer</p>
                    <span class="dev-specialty">Full Stack Specialist</span>
                    <div class="social-links">
                        <a href="https://github.com/marvelkn/uts-todolist-lab" target="_blank" class="social-link github">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="https://www.instagram.com/marvelkn/" target="_blank" class="social-link instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div class="developer-card" data-aos="fade-up" data-aos-delay="100">
                    <img src="src/surya.jpeg" alt="Surya Novensky Tinus" class="profile-image">
                    <h3 class="dev-name">Surya Novensky Tinus</h3>
                    <p class="dev-id">00000108624</p>
                    <p class="dev-role">Frontend Developer</p>
                    <span class="dev-specialty">UI/UX Expert</span>
                    <div class="social-links">
                        <a href="https://github.com/suryant-tinus" target="_blank" class="social-link github">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="https://www.instagram.com/surya.tinus/" target="_blank" class="social-link instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>

                <div class="developer-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="src/eufrat.jpeg" alt="Eufrat Algaws" class="profile-image">
                    <h3 class="dev-name">Eufrat Algaws</h3>
                    <p class="dev-id">00000106760</p>
                    <p class="dev-role">Backend Developer</p>
                    <span class="dev-specialty">Database Expert</span>
                    <div class="social-links">
                        <a href="https://github.com/eufraat" target="_blank" class="social-link github">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="https://www.instagram.com/eufratag/" target="_blank" class="social-link instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>

                <div class="developer-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="src/brodanu.jpeg" alt="Danu Farhan Ihromi" class="profile-image">
                    <h3 class="dev-name">Danu Farhan Ihromi</h3>
                    <p class="dev-id">00000103241</p>
                    <p class="dev-role">Project Manager</p>
                    <span class="dev-specialty">Scrum Master</span>
                    <div class="social-links">
                        <a href="https://github.com/danufarhanihromi" target="_blank" class="social-link github">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="https://www.instagram.com/danufrhn//" target="_blank" class="social-link instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
        `,
        width: '90%',
        padding: '2rem',
        background: '#f8f9fc',
        showConfirmButton: false,
        showCloseButton: true,
        customClass: {
            container: 'about-us-modal',
            popup: 'about-us-popup',
            closeButton: 'about-us-close'
        }
    });
});
        </script>

        <script>
            // View Toggle functionality
            const tasksContainer = document.getElementById('tasksContainer');
            const listViewBtn = document.getElementById('listViewBtn');
            const boxViewBtn = document.getElementById('boxViewBtn');

            // Set initial view (you can store user preference in localStorage)
            const currentView = localStorage.getItem('taskView') || 'list';
            tasksContainer.className = currentView + '-view';
            updateViewButtons();

            listViewBtn.addEventListener('click', () => {
                tasksContainer.className = 'list-view';
                localStorage.setItem('taskView', 'list');
                updateViewButtons();
            });

            boxViewBtn.addEventListener('click', () => {
                tasksContainer.className = 'box-view';
                localStorage.setItem('taskView', 'box');
                updateViewButtons();
            });

            function updateViewButtons() {
                const currentView = tasksContainer.className;
                listViewBtn.classList.toggle('active', currentView === 'list-view');
                boxViewBtn.classList.toggle('active', currentView === 'box-view');
            }
        </script>
</body>

</html>