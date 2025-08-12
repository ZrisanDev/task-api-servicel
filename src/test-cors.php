<?php
// test-cors.php - Archivo temporal para probar CORS

// Headers CORS muy permisivos para testing
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header(
    "Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With",
);
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Manejar preflight
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit(0);
}

// Información de debugging
$debug_info = [
    "message" => "CORS Test - Petición recibida correctamente",
    "method" => $_SERVER["REQUEST_METHOD"],
    "origin" => $_SERVER["HTTP_ORIGIN"] ?? "No origin header",
    "referer" => $_SERVER["HTTP_REFERER"] ?? "No referer header",
    "user_agent" => $_SERVER["HTTP_USER_AGENT"] ?? "No user agent",
    "headers" => getallheaders(),
    "timestamp" => date("Y-m-d H:i:s"),
];

echo json_encode($debug_info, JSON_PRETTY_PRINT);
?>
