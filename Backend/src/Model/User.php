<?php

namespace App\Model;

use App\Enum\Role;

class User {
    private ?int $id;
    private string $username;
    private string $password;
    private Role $role;
    private ?DateTime $createdAt;

    public function __construct(?int $id, string $username, string $password, Role $role, ?DateTime $createdAt = null) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
        $this->$createdAt = $createdAt;
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getUsername(): string { return $this->username; }
    public function setUsername(string $username): void { $this->username = $username; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): void { $this->password = $password; }

    public function getRole(): Role { return $this->role; }
    public function setRole(Role $role): void { $this->role = $role; }

    public function getCreatedAt(): ?DateTime { return $this->createdAt; }
    public function setCreatedAt(?DateTime $createdAt): void { $this->createdAt = $createdAt; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->role->value,
            'createdAt' => $this->createdAt
        ];
    }
}
