<?php
// helpers/EnvLoader.php

class EnvLoader
{
    private static $loaded = false;

    /**
     * Carga las variables de entorno desde el archivo .env
     */
    public static function load($path = null)
    {
        if (self::$loaded) {
            return; // Ya cargado, evitar duplicados
        }

        if ($path === null) {
            $path = dirname(__DIR__) . '/.env';
        }

        if (!file_exists($path)) {
            throw new Exception("Archivo .env no encontrado en: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parsear línea en formato KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                
                $name = trim($name);
                $value = trim($value);

                // Remover comillas si existen
                $value = trim($value, '"\'');

                // Establecer variable de entorno si no existe
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                    putenv("$name=$value");
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Obtener una variable de entorno con valor por defecto
     */
    public static function get($key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Obtener una variable de entorno requerida (lanza excepción si no existe)
     */
    public static function getRequired($key)
    {
        $value = self::get($key);
        
        if ($value === null || $value === '') {
            throw new Exception("Variable de entorno requerida no encontrada: $key");
        }

        return $value;
    }

    /**
     * Convertir string a booleano
     */
    public static function getBool($key, $default = false)
    {
        $value = self::get($key, $default);
        
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
    }

    /**
     * Convertir string a entero
     */
    public static function getInt($key, $default = 0)
    {
        return (int) self::get($key, $default);
    }

    /**
     * Obtener array desde string separado por comas
     */
    public static function getArray($key, $default = [])
    {
        $value = self::get($key);
        
        if (!$value) {
            return $default;
        }

        return array_map('trim', explode(',', $value));
    }
}
?>