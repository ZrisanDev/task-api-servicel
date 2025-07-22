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
        $tasks = $this->taskModel->getByProjectId($projectId);
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

        $success = $this->taskModel->create(
            $input["title"],
            $input["description"],
            $input["priority"],
            $input["dueDate"],
            $projectId, // Usar el projectId de la URL
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

        $success = $this->taskModel->update(
            $taskId,
            $input["title"],
            $input["description"],
            $input["priority"],
            $input["dueDate"],
            $input["status"],
            $input["projectId"],
        );

        if ($success) {
            Response::json(["message" => "Tarea actualizada exitosamente"]);
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
