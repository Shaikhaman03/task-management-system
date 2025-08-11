<?php
require_once 'TaskManager.php';

$taskManager = new TaskManager();
$message = '';
$errors = [];
$editTask = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $result = $taskManager->addTask(
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['date'] ?? '',
                $_POST['status'] ?? 'Pending'
            );
            
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $errors = $result['errors'];
            }
            break;
            
        case 'update':
            $result = $taskManager->updateTask(
                $_POST['id'],
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['date'] ?? '',
                $_POST['status'] ?? 'Pending'
            );
            
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $errors = $result['errors'];
            }
            break;
    }
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'edit':
            $id = $_GET['id'] ?? 0;
            $editTask = $taskManager->getTaskById($id);
            if (!$editTask) {
                $errors[] = 'Task not found.';
            }
            break;
            
        case 'delete':
            $id = $_GET['id'] ?? 0;
            $result = $taskManager->deleteTask($id);
            
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $errors = $result['errors'];
            }
            break;
    }
}

$tasks = $taskManager->getAllTasks();
$statusOptions = $taskManager->getStatusOptions();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Task Management System</h1>
            <p>Manage your tasks efficiently with our simple task management tool</p>
        </header>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Task Form -->
        <div class="form-section">
            <h2><?php echo $editTask ? 'Edit Task' : 'Add New Task'; ?></h2>
            
            <form method="POST" class="task-form">
                <input type="hidden" name="action" value="<?php echo $editTask ? 'update' : 'add'; ?>">
                <?php if ($editTask): ?>
                    <input type="hidden" name="id" value="<?php echo $editTask['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">Title *</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        value="<?php echo $editTask ? htmlspecialchars($editTask['title']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                    ><?php echo $editTask ? htmlspecialchars($editTask['description']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="date">Date *</label>
                    <input 
                        type="date" 
                        id="date" 
                        name="date" 
                        value="<?php echo $editTask ? $editTask['date'] : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <?php foreach ($statusOptions as $option): ?>
                            <option 
                                value="<?php echo $option; ?>"
                                <?php echo ($editTask && $editTask['status'] === $option) ? 'selected' : ''; ?>
                            >
                                <?php echo $option; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editTask ? 'Update Task' : 'Add Task'; ?>
                    </button>
                    
                    <?php if ($editTask): ?>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tasks Table -->
        <div class="table-section">
            <h2>All Tasks (<?php echo count($tasks); ?>)</h2>
            
            <?php if (empty($tasks)): ?>
                <div class="empty-state">
                    <p>No tasks found. Add your first task above!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="tasks-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><?php echo $task['id']; ?></td>
                                    <td class="task-title"><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td class="task-description">
                                        <?php 
                                        $desc = htmlspecialchars($task['description']);
                                        echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
                                        ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($task['date'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $taskManager->getStatusBadgeClass($task['status']); ?>">
                                            <?php echo $task['status']; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="?action=edit&id=<?php echo $task['id']; ?>" class="btn btn-edit">Edit</a>
                                        <a 
                                            href="?action=delete&id=<?php echo $task['id']; ?>" 
                                            class="btn btn-delete"
                                            onclick="return confirm('Are you sure you want to delete this task?')"
                                        >Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
