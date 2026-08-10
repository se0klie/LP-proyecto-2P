<?php

// --- Base de datos ---
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'espol_eventos');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

define('APP_ENV', getenv('APP_ENV') ?: 'development'); 
define('APP_TIMEZONE', 'America/Guayaquil');

date_default_timezone_set(APP_TIMEZONE);

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Sesiones (usadas por el middleware de autenticación)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
