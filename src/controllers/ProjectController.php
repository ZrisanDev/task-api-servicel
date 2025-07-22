<?php
// CONTROLLERS
require_once "controllers/AuthController.php";

// MODELS
require_once "models/Project.php";

// HELPERS
require_once "helpers/Response.php";


class ProjectController {
    private $projectModel;
    
    public function __construct() {
        $this->projectModel = new Project();
    }
    
    public function getAll() {
        AuthController::authenticate();
        $projects = $this->projectModel->getAll();
        Response::json($projects);
    }
    
    public function getBySlug($slug) {
        AuthController::authenticate();
        $project = $this->projectModel->findBySlug($slug);
        
        if (!$project) {
            Response::error('Proyecto no encontrado', 404);
        }
        
        Response::json($project);
    }
    
    public function create() {
        AuthController::authenticate();
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar campos requeridos
        if (!isset($input['name']) || empty(trim($input['name']))) {
            Response::error('El campo name es requerido', 400);
        }
        
        if (!isset($input['abbreviation']) || empty(trim($input['abbreviation']))) {
            Response::error('El campo abbreviation es requerido', 400);
        }
        
        $name = trim($input['name']);
        $abbreviation = trim($input['abbreviation']);
        
        // Validar longitud
        if (strlen($name) > 100) {
            Response::error('El name no puede exceder 100 caracteres', 400);
        }
        
        if (strlen($abbreviation) > 10) {
            Response::error('La abbreviation no puede exceder 10 caracteres', 400);
        }
        
        $project = $this->projectModel->create($name, $abbreviation);
        
        if ($project) {
            Response::json([
                'message' => 'Proyecto creado exitosamente',
                'project' => $project
            ], 201);
        } else {
            Response::error('Error al crear el proyecto', 500);
        }
    }
}
?>
