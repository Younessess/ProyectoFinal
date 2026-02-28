<?php

// 1. Cargar el Autoloader apuntando correctamente a la carpeta src
require_once __DIR__ . '/../src/Core/Autoloader.php'; 

use App\Core\Router;
use App\Controller\UserController;
use App\Enum\Role;

use App\Controller\PlayerController;
use App\Controller\MatchController;
use App\Controller\SeasonController;
use App\Controller\InjuryController;


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

// apis para jugadores

/* $router->add('GET', '/players', [PlayerController::class, 'getAllPlayers'], ['auth' => true]);
$router->add('GET', '/players/{id}', [PlayerController::class, 'getPlayerById'], ['auth' => true]);
$router->add('DELETE', '/players/{id}', [PlayerController::class, 'deletePlayer'], ['auth' => true, 'role' => Role::ADMIN]); */

// --- RUTAS DE PRUEBA (Sin autenticación) ---
// Estas rutas son solo para verificar que la base de datos devuelve datos
$router->add('GET', '/players', [PlayerController::class, 'getAllPlayers']);
$router->add('GET', '/players/{id}', [PlayerController::class, 'getPlayerById']);
$router->add('POST', '/players', [PlayerController::class, 'createPlayer']);
$router->add('PUT', '/players/{id}', [PlayerController::class, 'updatePlayer']);
$router->add('DELETE', '/players/{id}', [PlayerController::class, 'deletePlayer']);

// Estas son las que dejarás definitivas (coméntalas si te dan problemas ahora)
// $router->add('GET', '/players', [PlayerController::class, 'getAllPlayers'], ['auth' => true]);
// Rutas para partidos

$router->add('GET', '/matches', [MatchController::class, 'getAllMatches']);
$router->add('PUT', '/matches/{id}', [MatchController::class, 'updateMatch']/* , ['auth' => true] */);
$router->add('POST', '/matches', [MatchController::class, 'createMatch']/* , ['auth' => true] */);

// Rutas para temporadas

$router->add('GET', '/seasons', [SeasonController::class, 'getAll']);
$router->add('POST', '/seasons', [SeasonController::class, 'create']);
$router->add('PUT', '/seasons/{id}', [SeasonController::class, 'update']);
$router->add('DELETE', '/seasons/{id}', [SeasonController::class, 'delete']);

// rutas para lesiones
$router->add('POST', '/injuries', [InjuryController::class, 'create']);
$router->add('GET', '/injuries/active', [InjuryController::class, 'getActiveInjuries']);
$router->add('PUT', '/injuries/close', [InjuryController::class, 'close']);

// 3. Ejecutar
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);