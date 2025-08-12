<?php
// Cargar helper de variables de entorno
require_once __DIR__ . '/helpers/EnvLoader.php';

// Cargar variables de entorno
try {
    EnvLoader::load();
} catch (Exception $e) {
    // En producción podrías querer un manejo más elegante
    die("Error cargando configuración: " . $e->getMessage());
}

class Config 
{
    // Configuración de Base de Datos
    public static function getDbHost() {
        return EnvLoader::getRequired('DB_HOST');
    }
    
    public static function getDbName() {
        return EnvLoader::getRequired('DB_NAME');
    }
    
    public static function getDbUser() {
        return EnvLoader::getRequired('DB_USER');
    }
    
    public static function getDbPass() {
        return EnvLoader::getRequired('DB_PASS');
    }

    // Configuración JWT
    public static function getJwtSecret() {
        return EnvLoader::getRequired('JWT_SECRET');
    }
    
    public static function getJwtExpire() {
        return EnvLoader::getInt('JWT_EXPIRE', 3600);
    }

    // Configuración de aplicación
    public static function getAppEnv() {
        return EnvLoader::get('APP_ENV', 'production');
    }
    
    public static function isDebug() {
        return EnvLoader::getBool('APP_DEBUG', false);
    }

    // Dominios permitidos
    public static function getAllowedOrigins() {
        return EnvLoader::getArray('ALLOWED_ORIGINS', ['https://app.servicel.com']);
    }

    // Constantes para compatibilidad con código existente
    const DB_HOST = null; // Deprecated - usar getDbHost()
    const DB_NAME = null; // Deprecated - usar getDbName()
    const DB_USER = null; // Deprecated - usar getDbUser()
    const DB_PASS = null; // Deprecated - usar getDbPass()
    const JWT_SECRET = null; // Deprecated - usar getJwtSecret()
    const JWT_EXPIRE = null; // Deprecated - usar getJwtExpire()
}

// Para mantener compatibilidad temporal, puedes definir las constantes
// IMPORTANTE: Remover estas líneas después de actualizar todo el código
if (!defined('DB_HOST_COMPAT')) {
    define('DB_HOST_COMPAT', Config::getDbHost());
    define('DB_NAME_COMPAT', Config::getDbName());
    define('DB_USER_COMPAT', Config::getDbUser());
    define('DB_PASS_COMPAT', Config::getDbPass());
    define('JWT_SECRET_COMPAT', Config::getJwtSecret());
    define('JWT_EXPIRE_COMPAT', Config::getJwtExpire());
}
?>