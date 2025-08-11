<?php

class TaskManager {
    private $filename = 'task-lists.txt';
    
    public function __construct() {
        // Create file if it doesn't exist
        if (!file_exists($this->filename)) {
            file_put_contents($this->filename, '[]');
        }
    }
    
    /**
     * Get all tasks from file
     */
    public function getAllTasks() {
        $content = file_get_contents($this->filename);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * Get next available ID
     */
    private function getNextId() {
        $tasks = $this->getAllTasks();
        if (empty($tasks)) {
            return 1;
        }
        
        $maxId = max(array_column($tasks, 'id'));
        return $maxId + 1;
    }
    
    /**
     * Add new task
     */
    public function addTask($title, $description, $date, $status) {
        // Validation
        $errors = $this->validateTask($title, $date);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $tasks = $this->getAllTasks();
        
        $newTask = [
            'id' => $this->getNextId(),
            'title' => trim($title),
            'description' => trim($description),
            'date' => $date,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $tasks[] = $newTask;
        
        if ($this->saveTasks($tasks)) {
            return ['success' => true, 'message' => 'Task added successfully!'];
        } else {
            return ['success' => false, 'errors' => ['Failed to save task.']];
        }
    }
    
    /**
     * Get task by ID
     */
    public function getTaskById($id) {
        $tasks = $this->getAllTasks();
        foreach ($tasks as $task) {
            if ($task['id'] == $id) {
                return $task;
            }
        }
        return null;
    }
    
    /**
     * Update task
     */
    public function updateTask($id, $title, $description, $date, $status) {
        // Validation
        $errors = $this->validateTask($title, $date);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $tasks = $this->getAllTasks();
        $taskFound = false;
        
        for ($i = 0; $i < count($tasks); $i++) {
            if ($tasks[$i]['id'] == $id) {
                $tasks[$i]['title'] = trim($title);
                $tasks[$i]['description'] = trim($description);
                $tasks[$i]['date'] = $date;
                $tasks[$i]['status'] = $status;
                $tasks[$i]['updated_at'] = date('Y-m-d H:i:s');
                $taskFound = true;
                break;
            }
        }
        
        if (!$taskFound) {
            return ['success' => false, 'errors' => ['Task not found.']];
        }
        
        if ($this->saveTasks($tasks)) {
            return ['success' => true, 'message' => 'Task updated successfully!'];
        } else {
            return ['success' => false, 'errors' => ['Failed to update task.']];
        }
    }
    
    /**
     * Delete task
     */
    public function deleteTask($id) {
        $tasks = $this->getAllTasks();
        $taskFound = false;
        
        for ($i = 0; $i < count($tasks); $i++) {
            if ($tasks[$i]['id'] == $id) {
                array_splice($tasks, $i, 1);
                $taskFound = true;
                break;
            }
        }
        
        if (!$taskFound) {
            return ['success' => false, 'errors' => ['Task not found.']];
        }
        
        if ($this->saveTasks($tasks)) {
            return ['success' => true, 'message' => 'Task deleted successfully!'];
        } else {
            return ['success' => false, 'errors' => ['Failed to delete task.']];
        }
    }
    
    /**
     * Validate task data
     */
    private function validateTask($title, $date) {
        $errors = [];
        
        if (empty(trim($title))) {
            $errors[] = 'Title is required.';
        }
        
        if (empty($date)) {
            $errors[] = 'Date is required.';
        } elseif (!$this->isValidDate($date)) {
            $errors[] = 'Please enter a valid date.';
        }
        
        return $errors;
    }
    
    /**
     * Check if date is valid
     */
    private function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Save tasks to file
     */
    private function saveTasks($tasks) {
        $json = json_encode($tasks, JSON_PRETTY_PRINT);
        return file_put_contents($this->filename, $json) !== false;
    }
    
    /**
     * Get status options
     */
    public function getStatusOptions() {
        return ['Pending', 'In Progress', 'Completed'];
    }
    
    /**
     * Get status badge class
     */
    public function getStatusBadgeClass($status) {
        switch ($status) {
            case 'Pending':
                return 'status-pending';
            case 'In Progress':
                return 'status-progress';
            case 'Completed':
                return 'status-completed';
            default:
                return 'status-pending';
        }
    }
}
?>
