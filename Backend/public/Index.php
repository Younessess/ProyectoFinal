<?php

// 1. Cargar el Autoloader apuntando correctamente a la carpeta src
require_once __DIR__ . '/../src/Core/Autoloader.php'; 

use App\Core\Router;
use App\Controller\UserController;
use App\Enum\Role;

// 2. Iniciar sesión para el control de roles
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$router = new Router();

/**
 * RUTAS API
 */

// Como tu Index.php está en /public, la URL base será esa.
// Ruta para ver todos los usuarios (Solo Admin)
$router->add('GET', '/users', [UserController::class, 'getAllUsers'], [
    'auth' => true,
    'role' => Role::ADMIN
]);

// 3. Ejecutar
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);