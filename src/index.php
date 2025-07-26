<?php
require_once "Router.php";

// CONTROLLERS
require_once "controllers/AuthController.php";
require_once "controllers/TaskController.php";
require_once "controllers/ProjectController.php";

// HELPERS
require_once "helpers/Response.php";

// Headers CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit(0);
}

// Inicializar router
$router = new Router();

// Definir rutas
$router->add("POST", "/login", function () {
    $authController = new AuthController();
    $authController->login();
});

$router->add("POST", "/change-password", function () {
    $authController = new AuthController();
    $authController->changePassword();
});

$router->add("GET", "/projects", function () {
    $projectController = new ProjectController();
    $projectController->getAll();
});

$router->add("POST", "/projects", function () {
    $projectController = new ProjectController();
    $projectController->create();
});

$router->add("GET", "/projects/{slug}", function ($slug) {
    $projectController = new ProjectController();
    $projectController->getBySlug($slug);
});

$router->add("GET", "/projects/{projectId}/tasks", function ($projectId) {
    $taskController = new TaskController();
    $taskController->getByProjectId($projectId);
});

$router->add("POST", "/tasks/{projectId}", function ($projectId) {
    $taskController = new TaskController();
    $taskController->create($projectId);
});

$router->add("PATCH", "/tasks/{taskId}/status", function ($taskId) {
    $taskController = new TaskController();
    $taskController->updateStatus($taskId);
});

$router->add("PATCH", "/tasks/{taskId}/worked-hours", function ($taskId) {
    $taskController = new TaskController();
    $taskController->updateWorkedHours($taskId);
});

$router->add("PUT", "/tasks/{taskId}", function ($taskId) {
    $taskController = new TaskController();
    $taskController->update($taskId);
});

$router->add("DELETE", "/tasks/{taskId}", function ($taskId) {
    $taskController = new TaskController();
    $taskController->delete($taskId);
});

// Manejar errores globales
try {
    $router->dispatch();
} catch (Exception $e) {
    Response::error("Error interno del servidor: " . $e->getMessage(), 500);
}
?>
