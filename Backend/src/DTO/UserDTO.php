<?php

namespace App\DTO;

use App\Enum\Role;
use DateTime;

class UserDTO {
    public ?int $id;
    public string $username;
    public Role $role;
    public ?DateTime $createdAt;

    public function __construct(?int $id, string $username, Role $role, ?DateTime $createdAt = null) {
        $this->id = $id;
        $this->username = $username;
        $this->role = $role;
        $this->createdAt = $createdAt;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->role->value,
            // Formateamos la fecha para que el JSON sea legible
            'created_at' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
        ];
    }
}