<?php

namespace App\Repository;

use App\Model\User;
use App\Enum\Role;
use PDO;

class UserRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getUserById(int $id): ?User {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        new User((int)$row['id'], $row['username'], $row['password_hash'], Role::from($row['role']), new DateTime($row['created_at']));
    }

    public function getAllUsers(): array {
        $stmt = $this->db->query("SELECT * FROM users");
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User((int)$row['id'], $row['username'], $row['password_hash'], Role::from($row['role']), new DateTime($row['created_at']));
        }
        return $users;
    }

    public function createUser(User $user): bool {
        $stmt = $this->db->prepare("INSERT INTO users (username, password_hash, role, created_at) VALUES (:username, :password, :role, :created_at)");
        $result = $stmt->execute([
            'username' => $user->getUsername(),
            'password' => $user->getPassword(),
            'role' => $user->getRole()->value,
            'created_at' => $user->getCreatedAT(),

        ]);
        if ($result) {
            $user->setId((int)$this->db->lastInsertId());
        }
        return $result;
    }

    public function updateUser(User $user): bool {
        $stmt = $this->db->prepare("UPDATE users SET username = :username, password_hash = :password, role = :role, , created_at = :createdAT WHERE id = :id");
        return $stmt->execute([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'password' => $user->getPassword(),
            'role' => $user->getRole()->value,
            'createdAt' => $user->getCreatedAt(),
        ]);
    }

    public function deleteUser(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getUserByUsername(string $username): ?User {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return new User((int)$row['id'], $row['username'], $row['password_hash'], Role::from($row['role']), new DateTime($row['created_at']));
    }
}
