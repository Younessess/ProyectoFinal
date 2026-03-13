<?php
namespace App\Model;

use DateTime;

class Player {
    private ?int $id_player;
    private string $first_name;
    private string $last_name;
    private ?string $nickname;
    private ?string $usual_position;
    private string $status;
    private DateTime $created_at;
    private ?int $squad_number = null;

    public function __construct(?int $id, string $fname, string $lname, ?string $nick, ?string $pos, string $status = 'active', ?DateTime $created = null, ?int $squad_number) {
        $this->id_player = $id;
        $this->first_name = $fname;
        $this->last_name = $lname;
        $this->nickname = $nick;
        $this->usual_position = $pos;
        $this->status = $status;
        $this->created_at = $created ?? new DateTime();
        $this->squad_number = $squad_number;
    }

    // Getters
    public function getId(): ?int { return $this->id_player; }
    public function getFirstName(): string { return $this->first_name; }
    public function getLastName(): string { return $this->last_name; }
    public function getNickname(): ?string { return $this->nickname; }
    public function getUsualPosition(): ?string { return $this->usual_position; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): DateTime { return $this->created_at; }
    public function getSquadNumber(): ?int { return $this->squad_number; }

    // Setters (Para el Update)
    public function setFirstName(string $name): void { $this->first_name = $name; }
    public function setLastName(string $name): void { $this->last_name = $name; }
    public function setNickname(?string $nick): void { $this->nickname = $nick; }
    public function setUsualPosition(?string $pos): void { $this->usual_position = $pos; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setSquadNumber(?int $number): void { $this->squad_number = $number; }
}