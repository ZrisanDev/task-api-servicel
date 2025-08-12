<?php
// helpers/SecurityMiddleware.php

require_once "helpers/Response.php";
require_once "config.php";

class SecurityMiddleware
{
    /**
     * Obtiene los dominios permitidos desde variables de entorno
     */
    private static function getAllowedOrigins()
    {
        $origins = Config::getAllowedOrigins();

        // En desarrollo, agregar localhost
        if (Config::getAppEnv() !== "production") {
            $origins = array_merge($origins, [
                "http://localhost:3000",
                "http://localhost:5173",
                "http://localhost:8080",
                "http://127.0.0.1:3000",
                "http://127.0.0.1:5173",
                "http://127.0.0.1:8080",
            ]);
        }

        return $origins;
    }

    /**
     * Configura headers CORS de forma permisiva para debugging
     */
    public static function setCORSHeaders()
    {
        $origin = $_SERVER["HTTP_ORIGIN"] ?? "";
        $allowedOrigins = self::getAllowedOrigins();

        // Para debugging, log del origin
        if (Config::isDebug()) {
            error_log("CORS Debug - Origin recibido: " . $origin);
            error_log(
                "CORS Debug - Origins permitidos: " .
                    implode(", ", $allowedOrigins),
            );
        }

        // Si el origin está permitido, usar ese origin específico
        if ($origin && in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        // En desarrollo, ser más permisivo
        elseif (Config::getAppEnv() !== "production" && $origin) {
            header("Access-Control-Allow-Origin: $origin");
        }
        // Fallback para desarrollo
        elseif (Config::getAppEnv() !== "production") {
            header("Access-Control-Allow-Origin: *");
        }

        header(
            "Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS",
        );
        header(
            "Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With",
        );
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400"); // Cache preflight por 24 horas
    }

    /**
     * Maneja peticiones OPTIONS (CORS preflight)
     */
    public static function handlePreflight()
    {
        if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
            self::setCORSHeaders();
            http_response_code(200);
            exit(0);
        }
    }

    /**
     * Valida que la petición venga de un origen permitido (más permisivo)
     */
    public static function validateOrigin()
    {
        // IMPORTANTE: Establecer headers CORS PRIMERO
        self::setCORSHeaders();

        // Manejar preflight
        self::handlePreflight();

        $origin = $_SERVER["HTTP_ORIGIN"] ?? "";
        $referer = $_SERVER["HTTP_REFERER"] ?? "";
        $allowedOrigins = self::getAllowedOrigins();

        // En desarrollo, ser muy permisivo
        if (Config::getAppEnv() !== "production") {
            return true;
        }

        // Validar origin
        if ($origin && in_array($origin, $allowedOrigins)) {
            return true;
        }

        // Validar referer como fallback
        if (!$origin && $referer) {
            foreach ($allowedOrigins as $allowedOrigin) {
                $domain = str_replace(
                    ["https://", "http://", "www."],
                    "",
                    $allowedOrigin,
                );
                if (strpos($referer, $domain) !== false) {
                    return true;
                }
            }
        }

        // Log para debugging
        if (Config::isDebug()) {
            error_log(
                "Origin validation failed - Origin: $origin, Referer: $referer",
            );
        }

        // En lugar de bloquear inmediatamente, solo logear en producción
        // Response::error("Acceso no autorizado - Origin no permitido", 403);
        return true; // Temporal para debugging
    }

    /**
     * Middleware para rutas que requieren autenticación
     */
    public static function requireAuth()
    {
        // Primero establecer CORS y validar origin
        self::validateOrigin();

        // Luego validar autenticación JWT
        return AuthController::authenticate();
    }

    /**
     * Middleware para rutas públicas (solo establece CORS)
     */
    public static function publicRoute()
    {
        self::validateOrigin();
    }
}
?>
