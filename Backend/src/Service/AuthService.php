<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Model\User;
use App\Enum\Role;
use App\DTO\UserDTO;
use DateTime;

class AuthService {
    private UserRepository $userRepository;
    private UserService $userService;

    public function __construct(UserRepository $userRepository, UserService $userService) {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    /**
     * Crea un nuevo usuario (Entrenador o Analista).
     * Solo accesible para el Administrador.
     */
    public function register(array $data): bool {
        // SEGURIDAD: Solo el Admin puede crear otros usuarios
        if (!self::isAdmin()) {
            throw new \Exception("Acceso denegado: Solo el administrador puede dar de alta a técnicos.");
        }

        if (empty($data['password'])) {
            throw new \Exception("La contraseña es obligatoria");
        }

        $hashedPassword = \password_hash($data['password'], PASSWORD_BCRYPT);
        
        // El Admin define el rol desde el formulario
        $role = isset($data['role']) ? Role::from($data['role']) : Role::ANALYST;

        $user = new User(
            null,
            $data['username'],
            $hashedPassword,
            $role,
            new DateTime()
        );

        return $this->userRepository->createUser($user);
    }

    /**
     * Autentica a un usuario y gestiona la sesión.
     */
    public function login(string $username, string $password): ?UserDTO {
        $user = $this->userRepository->getUserByUsername($username);
        
        if ($user && \password_verify($password, $user->getPassword())) {
            // Iniciamos sesión si no está iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_role'] = $user->getRole()->value; 
            $_SESSION['username'] = $user->getUsername();

            return new UserDTO(
                $user->getId(),
                $user->getUsername(),
                $user->getRole(),
                $user->getCreatedAt()
            );
        }

        return null;
    }

    /**
     * Cierra la sesión y limpia los datos.
     */
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        \session_unset();
        \session_destroy();
    }

    /**
     * Verifica si hay un usuario autenticado.
     */
    public static function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    /**
     * Verifica si el usuario en sesión tiene un rol específico.
     */
    public static function hasRole(Role $role): bool {
        return self::isLoggedIn() && $_SESSION['user_role'] === $role->value;
    }

    /**
     * Helpers de control de acceso para las vistas.
     */
    public static function isAdmin(): bool {
        return self::hasRole(Role::ADMIN);
    }

    public static function isCoach(): bool {
        return self::hasRole(Role::COACH);
    }

    public static function isMedical(): bool {
        return self::hasRole(Role::MEDICAL);
    }

    /**
     * Retorna los datos básicos del usuario actual en sesión.
     */
    public static function getCurrentUserSession(): array {
        if (!self::isLoggedIn()) return [];
        return [
            'id' => $_SESSION['user_id'],
            'role' => $_SESSION['user_role'],
            'username' => $_SESSION['username']
        ];
    }
}