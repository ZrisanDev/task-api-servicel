<?php

require_once "database/Database.php";

class Task
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByProjectId($projectId)
    {
        $stmt = $this->db->prepare("
            SELECT t.*, p.name as project_name
            FROM tasks t
            JOIN projects p ON t.projectId = p.id
            WHERE t.projectId = ?
            ORDER BY t.priority DESC, t.dueDate ASC
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function create(
        $title,
        $description,
        $priority,
        $dueDate,
        $projectId,
    ) {
        $id = UUID::generate();
        $stmt = $this->db->prepare("
            INSERT INTO tasks (id, title, description, priority, dueDate, status, projectId)
            VALUES (?, ?, ?, ?, ?, 'backlog', ?)
        ");
        return $stmt->execute([
            $id,
            $title,
            $description,
            $priority,
            $dueDate,
            $projectId,
        ]);
    }

    public function updateStatus($taskId, $status)
    {
        $stmt = $this->db->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $taskId]);
    }

    public function update(
        $taskId,
        $title,
        $description,
        $priority,
        $dueDate,
        $status,
        $projectId,
    ) {
        $stmt = $this->db->prepare("
            UPDATE tasks
            SET title = ?, description = ?, priority = ?, dueDate = ?, status = ?, projectId = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $title,
            $description,
            $priority,
            $dueDate,
            $status,
            $projectId,
            $taskId,
        ]);
    }

    public function findById($taskId)
    {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        return $stmt->fetch();
    }

    public function delete($taskId)
    {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$taskId]);
    }
}
?>
