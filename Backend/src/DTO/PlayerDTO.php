<?php
namespace App\DTO;

class PlayerDTO {
    public ?int $id;
    public string $first_name;
    public string $last_name;
    public ?string $nickname;
    public ?string $position;
    public string $status;

    public function __construct(?int $id, string $fname, string $lname, ?string $nick, ?string $pos, string $status) {
        $this->id = $id;
        $this->first_name = $fname;
        $this->last_name = $lname;
        $this->nickname = $nick;
        $this->position = $pos;
        $this->status = $status;
    }

    public function toArray(): array {
        return [
            'id_player' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'nickname' => $this->nickname,
            'usual_position' => $this->position,
            'status' => $this->status
        ];
    }
}