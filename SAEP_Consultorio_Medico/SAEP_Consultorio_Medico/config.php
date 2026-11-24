<?php
declare(strict_types=1);

/*
 * Configure estas variáveis no ambiente do Apache/PHP.
 * APP_DB_HOST, APP_DB_NAME, APP_DB_USER, APP_DB_PASS e CPF_ENCRYPTION_KEY.
 * CPF_ENCRYPTION_KEY deve ser uma chave aleatória de 32 bytes em hexadecimal (64 caracteres).
 * Gere uma com: php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
 * Não coloque a chave no banco, no SQL ou no repositório.
 */
define('DB_HOST', getenv('APP_DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('APP_DB_NAME') ?: 'saep_db');
define('DB_USER', getenv('APP_DB_USER') ?: 'root');
define('DB_PASS', getenv('APP_DB_PASS') ?: '12345678');

$keyHex = getenv('CPF_ENCRYPTION_KEY') ?: '8f4e2a9c7d1b6e035a8c4f1d9e2b7a603c5d8e1f4a9b2c7d6e0f1a3b5c8d9e2f';
if (!preg_match('/^[a-fA-F0-9]{64}$/', $keyHex)) {
    http_response_code(500);
    exit('Configuração ausente: defina CPF_ENCRYPTION_KEY com 64 caracteres hexadecimais.');
}
define('CPF_ENCRYPTION_KEY', hex2bin($keyHex));

define('SESSION_TIMEOUT', 900); // 15 minutos
date_default_timezone_set('America/Sao_Paulo');

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados. Confira as configurações do MySQL.');
}
