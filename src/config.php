<?php
class Config {
    const DB_HOST = 'mysql';  // Nombre del servicio en docker-compose
    const DB_NAME = 'project_management';
    const DB_USER = 'api_user';
    const DB_PASS = 'api_password_123';
    const JWT_SECRET = 'fo2RKn2MSpchbV!jg^@DGfUy39MUz4';
    const JWT_EXPIRE = 3600; // 1 hora
}
?>