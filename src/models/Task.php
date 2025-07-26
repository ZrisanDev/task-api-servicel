<?php

require_once "database/Database.php";

class Task
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByProjectId($projectId, $month = null, $year = null)
    {
        // Query base
        $sql = "
            SELECT t.*, p.name as project_name
            FROM tasks t
            JOIN projects p ON t.projectId = p.id
            WHERE t.projectId = ?
        ";

        $params = [$projectId];

        // Agregar filtros de fecha si se proporcionan
        if ($month) {
            // Formato de $month: "YYYY-MM"
            $sql .= " AND DATE_FORMAT(t.dueDate, '%Y-%m') = ?";
            $params[] = $month;
        } elseif ($year) {
            // Si solo se proporciona año, filtrar por todo el año
            $sql .= " AND YEAR(t.dueDate) = ?";
            $params[] = $year;
        }

        // Ordenar por prioridad y fecha
        $sql .= " ORDER BY t.priority DESC, t.dueDate ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(
        $title,
        $description,
        $priority,
        $dueDate,
        $projectId,
        $assignee = null,
    ) {
        $id = UUID::generate();
        $stmt = $this->db->prepare("
            INSERT INTO tasks (id, title, description, priority, dueDate, status, projectId, assignee, workedHours, completedDate, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 'backlog', ?, ?, 0, NULL, NOW(), NOW())
        ");
        return $stmt->execute([
            $id,
            $title,
            $description,
            $priority,
            $dueDate,
            $projectId,
            $assignee,
        ]);
    }

    public function updateStatus($taskId, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE tasks
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$status, $taskId]);
    }

    public function updateWorkedHours($taskId, $workedHours)
    {
        // Actualizar workedHours y completedDate si es la primera vez que se registran horas
        $stmt = $this->db->prepare("
            UPDATE tasks
            SET workedHours = ?,
                completedDate = CASE
                    WHEN workedHours = 0 AND ? > 0 THEN NOW()
                    ELSE completedDate
                END,
                updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$workedHours, $workedHours, $taskId]);
    }

    public function update(
        $taskId,
        $title,
        $description,
        $priority,
        $dueDate,
        $status,
        $projectId,
        $assignee = null,
        $workedHours = 0,
    ) {
        $stmt = $this->db->prepare("
            UPDATE tasks
            SET title = ?,
                description = ?,
                priority = ?,
                dueDate = ?,
                status = ?,
                projectId = ?,
                assignee = ?,
                workedHours = ?,
                completedDate = CASE
                    WHEN status != 'completed' AND ? = 'completed' AND workedHours > 0 THEN NOW()
                    WHEN ? != 'completed' THEN NULL
                    ELSE completedDate
                END,
                updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([
            $title,
            $description,
            $priority,
            $dueDate,
            $status,
            $projectId,
            $assignee,
            $workedHours,
            $status, // Para la primera condición CASE
            $status, // Para la segunda condición CASE
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

    // Método adicional: obtener estadísticas de horas trabajadas por proyecto/mes
    public function getWorkedHoursStats($projectId, $month = null, $year = null)
    {
        $sql = "
            SELECT
                COUNT(*) as totalTasks,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completedTasks,
                SUM(CASE WHEN status = 'completed' THEN workedHours ELSE 0 END) as totalWorkedHours,
                AVG(CASE WHEN status = 'completed' AND workedHours > 0 THEN workedHours ELSE NULL END) as avgWorkedHours
            FROM tasks
            WHERE projectId = ?
        ";

        $params = [$projectId];

        if ($month) {
            $sql .= " AND DATE_FORMAT(dueDate, '%Y-%m') = ?";
            $params[] = $month;
        } elseif ($year) {
            $sql .= " AND YEAR(dueDate) = ?";
            $params[] = $year;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // Método adicional: obtener tareas completadas con detalles para reportes
    public function getCompletedTasksWithHours(
        $projectId,
        $month = null,
        $year = null,
    ) {
        $sql = "
            SELECT id, title, workedHours, completedDate, dueDate, assignee
            FROM tasks
            WHERE projectId = ? AND status = 'completed' AND workedHours > 0
        ";

        $params = [$projectId];

        if ($month) {
            $sql .= " AND DATE_FORMAT(dueDate, '%Y-%m') = ?";
            $params[] = $month;
        } elseif ($year) {
            $sql .= " AND YEAR(dueDate) = ?";
            $params[] = $year;
        }

        $sql .= " ORDER BY completedDate DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>
