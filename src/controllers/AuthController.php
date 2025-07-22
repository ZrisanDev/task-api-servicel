<?php
// MODELS
require_once "models/User.php";

// HELPERS
require_once "helpers/Response.php";
require_once "helpers/JWT.php";

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input["username"]) || !isset($input["password"])) {
            Response::error("Username y password son requeridos", 400);
        }

        $user = $this->userModel->findByUsername($input["username"]);

        if (!$user || !password_verify($input["password"], $user["password"])) {
            Response::error("Credenciales inválidas", 401);
        }

        $payload = [
            "user_id" => $user["id"],
            "username" => $user["username"],
            "exp" => time() + Config::JWT_EXPIRE,
        ];

        $token = JWT::encode($payload, Config::JWT_SECRET);

        Response::json([
            "token" => $token,
            "user" => [
                "id" => $user["id"],
                "username" => $user["username"],
                "email" => $user["email"],
            ],
        ]);
    }

    public static function authenticate()
    {
        $headers = getallheaders();
        $authHeader = $headers["Authorization"] ?? null;

        if (
            !$authHeader ||
            !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)
        ) {
            Response::error("Token de autorización requerido", 401);
        }

        $token = $matches[1];
        $payload = JWT::decode($token, Config::JWT_SECRET);

        if (!$payload) {
            Response::error("Token inválido o expirado", 401);
        }

        return $payload;
    }

    private function validatePassword($password)
    {
        // Requisitos mínimos de contraseña
        // - Al menos 8 caracteres
        // - Al menos una mayúscula
        // - Al menos una minúscula
        // - Al menos un número

        if (strlen($password) < 8) {
            return false;
        }

        if (!preg_match("/[A-Z]/", $password)) {
            return false;
        }

        if (!preg_match("/[a-z]/", $password)) {
            return false;
        }

        if (!preg_match("/[0-9]/", $password)) {
            return false;
        }

        return true;
    }

    public function changePassword()
    {
        // Verificar autenticación
        $user = self::authenticate();

        $input = json_decode(file_get_contents("php://input"), true);

        // Validar que todos los campos estén presentes
        if (
            !isset($input["currentPassword"]) ||
            !isset($input["newPassword"]) ||
            !isset($input["confirmPassword"])
        ) {
            Response::error(
                "Contraseña actual, nueva contraseña y confirmación son requeridas",
                400,
            );
        }

        $currentPassword = trim($input["currentPassword"]);
        $newPassword = trim($input["newPassword"]);
        $confirmPassword = trim($input["confirmPassword"]);

        // Validar que no estén vacías
        if (
            empty($currentPassword) ||
            empty($newPassword) ||
            empty($confirmPassword)
        ) {
            Response::error(
                "Los campos de contraseña no pueden estar vacíos",
                400,
            );
        }

        // Validar que las contraseñas coincidan
        if ($newPassword !== $confirmPassword) {
            Response::error(
                "La nueva contraseña y su confirmación no coinciden",
                400,
            );
        }

        // Validar requisitos de la nueva contraseña
        if (!$this->validatePassword($newPassword)) {
            Response::error(
                "La nueva contraseña no cumple con los requisitos mínimos: debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número",
                422,
            );
        }

        // Obtener el usuario actual de la base de datos
        $currentUser = $this->userModel->findById($user["user_id"]);

        if (!$currentUser) {
            Response::error("Usuario no encontrado", 404);
        }

        // Verificar la contraseña actual
        if (!password_verify($currentPassword, $currentUser["password"])) {
            Response::error("Contraseña actual incorrecta", 401);
        }

        // Verificar que la nueva contraseña sea diferente a la actual
        if (password_verify($newPassword, $currentUser["password"])) {
            Response::error(
                "La nueva contraseña debe ser diferente a la actual",
                400,
            );
        }

        // Actualizar la contraseña
        $success = $this->userModel->updatePassword(
            $user["user_id"],
            $newPassword,
        );

        if (!$success) {
            Response::error("Error al actualizar la contraseña", 500);
        }

        // Opcional: Registrar el cambio de contraseña para auditoría
        $this->userModel->logPasswordChange($user["user_id"]);

        Response::json([
            "message" => "Contraseña actualizada exitosamente",
        ]);
    }
}

?>
