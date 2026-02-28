<?php
namespace App\DTO;

class InjuryDTO {
    public ?int $id;
    public int $id_player;
    public string $startDate;
    public ?string $endDate;
    public string $type;
    public string $severity;
    public ?string $expectedReturn;
    public ?string $observations;

    public function __construct($id, $player, $start, $end, $type, $sev, $ret, $obs) {
        $this->id = $id;
        $this->id_player = $player;
        $this->startDate = $start;
        $this->endDate = $end;
        $this->type = $type ?? 'No especificado';
        $this->severity = $sev ?? 'Media';
        $this->expectedReturn = $ret;
        $this->observations = $obs;
    }

    public function toArray(): array {
        return [
            'id_injury' => $this->id,
            'id_player' => $this->id_player,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'injury_type' => $this->type,
            'severity' => $this->severity,
            'expected_return_date' => $this->expectedReturn,
            'observations' => $this->observations
        ];
    }
}