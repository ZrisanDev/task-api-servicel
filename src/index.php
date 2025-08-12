<?php
require_once "Router.php";

// CONTROLLERS
require_once "controllers/AuthController.php";
require_once "controllers/TaskController.php";
require_once "controllers/ProjectController.php";

// HELPERS
require_once "helpers/Response.php";
require_once "helpers/SecurityMiddleware.php";

// =================== MANEJO UNIVERSAL DE CORS ===================
// Esto debe ejecutarse SIEMPRE, antes que cualquier otra cosa

// Establecer headers CORS dinámicamente
$origin = $_SERVER["HTTP_ORIGIN"] ?? "";
$allowed_origins = [
    "https://info.servicelperu.com",
    "https://www.info.servicelperu.com",
    // "http://localhost:3000",
    // "http://localhost:5173",
];

// Si el origin está permitido, usar ese origin específico
if ($origin && in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // En desarrollo, ser permisivo
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header(
    "Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With",
);
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400");
header("Content-Type: application/json");

// MANEJAR TODAS LAS PETICIONES OPTIONS INMEDIATAMENTE
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    echo json_encode(["preflight" => "OK", "timestamp" => date("Y-m-d H:i:s")]);
    exit(0);
}

// =================== DESPUÉS DE CORS, CONTINUAR NORMAL ===================

// Inicializar router
$router = new Router();

// =================== RUTAS PÚBLICAS ===================
$router->add("POST", "/login", function () {
    // Ya no necesitamos SecurityMiddleware::publicRoute() porque CORS se maneja arriba
    $authController = new AuthController();
    $authController->login();
});

// =================== RUTAS PROTEGIDAS ===================
$router->add("POST", "/change-password", function () {
    $user = AuthController::authenticate(); // Solo autenticación JWT
    $authController = new AuthController();
    $authController->changePassword();
});

$router->add("GET", "/projects", function () {
    $user = AuthController::authenticate();
    $projectController = new ProjectController();
    $projectController->getAll();
});

$router->add("POST", "/projects", function () {
    $user = AuthController::authenticate();
    $projectController = new ProjectController();
    $projectController->create();
});

$router->add("GET", "/projects/{slug}", function ($slug) {
    $user = AuthController::authenticate();
    $projectController = new ProjectController();
    $projectController->getBySlug($slug);
});

$router->add("GET", "/projects/{projectId}/tasks", function ($projectId) {
    $user = AuthController::authenticate();
    $taskController = new TaskController();
    $taskController->getByProjectId($projectId);
});

$router->add("POST", "/tasks/{projectId}", function ($projectId) {
    $user = AuthController::authenticate();
    $taskController = new TaskController();
    $taskController->create($projectId);
});

$router->add("PATCH", "/tasks/{taskId}/status", function ($taskId) {
    $user = AuthController::authenticate();
    $taskController = new TaskController();
    $taskController->updateStatus($taskId);
});

$router->add("PATCH", "/tasks/{taskId}/worked-hours", function ($taskId) {
    $user = AuthController::authenticate();
    $taskController = new TaskController();
    $taskController->updateWorkedHours($taskId);
});

$router->add("PUT", "/tasks/{taskId}", function ($taskId) {
    $user = AuthController::authenticate();
    $taskController = new TaskController();
    $taskController->update($taskId);
});

$router->add("DELETE", "/tasks/{taskId}", function ($taskId) {
    $user = AuthController::authenticate();
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
