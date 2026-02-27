<?php

namespace App\Core;

use App\Service\AuthService;
use App\Enum\Role;

class Router {
    private array $routes = [];

    /**
     * Añade una ruta al sistema.
     * @param string $method GET, POST, etc.
     * @param string $path Ruta (ej: /player/{id})
     * @param callable|array $handler [Controlador, Metodo] o función anónima.
     * @param array $options Configuración de seguridad ['auth' => true, 'role' => Role::ADMIN]
     */
    public function add(string $method, string $path, callable|array $handler, array $options = []): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'options' => $options
        ];
    }

    /**
     * Busca la ruta que coincide y ejecuta el controlador.
     */
    public function dispatch(string $method, string $uri): void {
        // Limpiamos la URI de parámetros GET (?id=1)
        $uri = \explode('?', $uri)[0];

        foreach ($this->routes as $route) {
            // Convertimos {id} en una expresión regular capture group
            $pattern = \preg_replace('/\{(\w+)\}/', '([^/]+)', $route['path']);
            $pattern = "#^" . $pattern . "$#";

            if ($route['method'] === $method && \preg_match($pattern, $uri, $matches)) {
                
                // 1. Verificación de Autorización y Roles
                if (!$this->checkAuthorization($route['options'])) {
                    return;
                }

                // Quitamos el primer elemento (la URI completa) para dejar solo los parámetros
                \array_shift($matches);
                
                $handler = $route['handler'];

                if (\is_array($handler)) {
                    [$controllerClass, $action] = $handler;
                    
                    // Verificamos si la clase existe antes de instanciar
                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        \call_user_func_array([$controller, $action], $matches);
                    } else {
                        $this->sendError(500, "Controller $controllerClass not found");
                    }
                } else {
                    \call_user_func_array($handler, $matches);
                }
                return;
            }
        }

        $this->sendNotFound();
    }

    /**
     * Lógica de seguridad integrada en el Router.
     */
    private function checkAuthorization(array $options): bool {
        // Si la ruta requiere autenticación
        if (isset($options['auth']) && $options['auth'] === true) {
            if (!AuthService::isLoggedIn()) {
                $this->sendUnauthorized('Authentication required');
                return false;
            }
        }

        // Si la ruta requiere un rol específico
        if (isset($options['role'])) {
            $requiredRole = $options['role'] instanceof Role 
                ? $options['role'] 
                : Role::from($options['role']);

            if (!AuthService::hasRole($requiredRole)) {
                $this->sendForbidden('Insufficient permissions');
                return false;
            }
        }

        return true;
    }

    private function sendUnauthorized(string $message): void {
        \http_response_code(401);
        \header('Content-Type: application/json');
        echo \json_encode(['error' => $message]);
    }

    private function sendForbidden(string $message): void {
        \http_response_code(403);
        \header('Content-Type: application/json');
        echo \json_encode(['error' => $message]);
    }

    private function sendNotFound(): void {
        \http_response_code(404);
        \header('Content-Type: application/json');
        echo \json_encode(['error' => 'Route not found']);
    }

    private function sendError(int $code, string $message): void {
        \http_response_code($code);
        \header('Content-Type: application/json');
        echo \json_encode(['error' => $message]);
    }
}