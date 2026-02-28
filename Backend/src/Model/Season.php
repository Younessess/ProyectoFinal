<?php
namespace App\Model;

use DateTime;

class Season {
    private ?int $id_season;
    private string $name;
    private ?DateTime $start_date;
    private ?DateTime $end_date;
    private bool $is_active;

    public function __construct(?int $id, string $name, ?string $start, ?string $end, int $active = 0) {
        $this->id_season = $id;
        $this->name = $name;
        $this->start_date = $start ? new DateTime($start) : null;
        $this->end_date = $end ? new DateTime($end) : null;
        $this->is_active = (bool)$active;
    }

    // Getters
    public function getId(): ?int { return $this->id_season; }
    public function getName(): string { return $this->name; }
    public function getStartDate(): ?DateTime { return $this->start_date; }
    public function getEndDate(): ?DateTime { return $this->end_date; }
    public function isActive(): bool { return $this->is_active; }
}