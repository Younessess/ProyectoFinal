<?php
namespace App\DTO;

class SeasonDTO {
    public ?int $id;
    public string $name;
    public string $startDate;
    public string $endDate;
    public bool $isActive;

    public function __construct(?int $id, string $name, string $start, string $end, bool $active) {
        $this->id = $id;
        $this->name = $name;
        $this->startDate = $start;
        $this->endDate = $end;
        $this->isActive = $active;
    }

    public function toArray(): array {
        return [
            'id_season' => $this->id,
            'name' => $this->name,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'is_active' => $this->isActive
        ];
    }
}