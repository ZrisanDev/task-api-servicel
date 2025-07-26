<?php
// CONTROLLERS
require_once "controllers/AuthController.php";

// MODELS
require_once "models/Task.php";
require_once "models/Project.php";

// HELPERS
require_once "helpers/Response.php";

class TaskController
{
    private $taskModel;
    private $projectModel;

    public function __construct()
    {
        $this->taskModel = new Task();
        $this->projectModel = new Project();
    }

    public function getByProjectId($projectId)
    {
        AuthController::authenticate();

        // Obtener parámetros de filtro de fecha desde query string
        $month = isset($_GET["month"]) ? $_GET["month"] : null;
        $year = isset($_GET["year"]) ? intval($_GET["year"]) : null;

        // Validar formato de mes si se proporciona (YYYY-MM)
        if ($month && !preg_match('/^\d{4}-\d{2}$/', $month)) {
            Response::error("Formato de mes inválido. Use YYYY-MM", 400);
        }

        // Validar año si se proporciona
        if ($year && ($year < 2020 || $year > date("Y"))) {
            Response::error(
                "Año inválido. Debe estar entre 2020 y " . date("Y"),
                400,
            );
        }

        $tasks = $this->taskModel->getByProjectId($projectId, $month, $year);
        Response::json($tasks);
    }

    public function create($projectId)
    {
        AuthController::authenticate();
        $input = json_decode(file_get_contents("php://input"), true);

        // Validar que el proyecto existe
        if (!$this->projectModel->findById($projectId)) {
            Response::error("Proyecto no encontrado", 404);
        }

        $required = ["title", "description", "priority", "dueDate"];
        foreach ($required as $field) {
            if (!isset($input[$field])) {
                Response::error("Campo $field es requerido", 400);
            }
        }

        // assignee es opcional
        $assignee = isset($input["assignee"]) ? trim($input["assignee"]) : null;
        if ($assignee === "") {
            $assignee = null;
        } // Convertir string vacío a null

        $success = $this->taskModel->create(
            $input["title"],
            $input["description"],
            $input["priority"],
            $input["dueDate"],
            $projectId,
            $assignee,
        );

        if ($success) {
            Response::json(["message" => "Tarea creada exitosamente"], 201);
        } else {
            Response::error("Error al crear la tarea", 500);
        }
    }

    public function updateStatus($taskId)
    {
        AuthController::authenticate();
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input["status"])) {
            Response::error("Status es requerido", 400);
        }

        $validStatuses = ["backlog", "in-progress", "testing", "completed"];
        if (!in_array($input["status"], $validStatuses)) {
            Response::error("Status inválido", 400);
        }

        $task = $this->taskModel->findById($taskId);
        if (!$task) {
            Response::error("Tarea no encontrada", 404);
        }

        $success = $this->taskModel->updateStatus($taskId, $input["status"]);

        if ($success) {
            Response::json(["message" => "Status actualizado exitosamente"]);
        } else {
            Response::error("Error al actualizar el status", 500);
        }
    }

    public function updateWorkedHours($taskId)
    {
        AuthController::authenticate();
        $input = json_decode(file_get_contents("php://input"), true);

        // Validar que workedHours esté presente
        if (!isset($input["workedHours"])) {
            Response::error("workedHours es requerido", 400);
        }

        // Validar que sea un número válido y positivo
        $workedHours = floatval($input["workedHours"]);
        if ($workedHours < 0) {
            Response::error(
                "Las horas trabajadas deben ser un número positivo",
                400,
            );
        }

        // Verificar que la tarea existe
        $task = $this->taskModel->findById($taskId);
        if (!$task) {
            Response::error("Tarea no encontrada", 404);
        }

        // Actualizar las horas trabajadas
        $success = $this->taskModel->updateWorkedHours($taskId, $workedHours);

        if ($success) {
            // Retornar la tarea actualizada
            $updatedTask = $this->taskModel->findById($taskId);
            Response::json([
                "message" => "Horas trabajadas actualizadas exitosamente",
                "task" => $updatedTask,
            ]);
        } else {
            Response::error("Error al actualizar las horas trabajadas", 500);
        }
    }

    public function update($taskId)
    {
        AuthController::authenticate();
        $input = json_decode(file_get_contents("php://input"), true);

        $required = [
            "title",
            "description",
            "priority",
            "dueDate",
            "status",
            "projectId",
        ];
        foreach ($required as $field) {
            if (!isset($input[$field])) {
                Response::error("Campo $field es requerido", 400);
            }
        }

        $task = $this->taskModel->findById($taskId);
        if (!$task) {
            Response::error("Tarea no encontrada", 404);
        }

        // assignee y workedHours son opcionales
        $assignee = isset($input["assignee"]) ? $input["assignee"] : null;
        $workedHours = isset($input["workedHours"])
            ? floatval($input["workedHours"])
            : 0;

        // Validar horas trabajadas
        if ($workedHours < 0) {
            Response::error(
                "Las horas trabajadas deben ser un número positivo",
                400,
            );
        }

        $success = $this->taskModel->update(
            $taskId,
            $input["title"],
            $input["description"],
            $input["priority"],
            $input["dueDate"],
            $input["status"],
            $input["projectId"],
            $assignee,
            $workedHours,
        );

        if ($success) {
            $updatedTask = $this->taskModel->findById($taskId);
            Response::json([
                "message" => "Tarea actualizada exitosamente",
                "task" => $updatedTask,
            ]);
        } else {
            Response::error("Error al actualizar la tarea", 500);
        }
    }

    public function delete($taskId)
    {
        AuthController::authenticate();

        // Verificar que la tarea existe
        $task = $this->taskModel->findById($taskId);
        if (!$task) {
            Response::error("Tarea no encontrada", 404);
        }

        $success = $this->taskModel->delete($taskId);

        if ($success) {
            Response::json(["message" => "Tarea eliminada exitosamente"]);
        } else {
            Response::error("Error al eliminar la tarea", 500);
        }
    }
}

?>
