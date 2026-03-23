<?php
namespace App\Model;

use DateTime;

class Injury {
    private ?int $id_injury;
    private int $id_player;
    private ?string $name_player;
    private DateTime $start_date;
    private ?DateTime $end_date;
    private ?string $injury_type;
    private ?string $severity;
    private ?DateTime $expected_return_date;
    private ?string $observations;

    public function __construct(
        ?int $id, 
        int $id_player,
        ?string $name_player,
        string $start, 
        ?string $end, 
        ?string $type, 
        ?string $severity, 
        ?string $expected, 
        ?string $obs
    ) {
        $this->id_injury = $id;
        $this->id_player = $id_player;
        $this->name_player = $name_player;
        $this->start_date = new DateTime($start);
        $this->end_date = $end ? new DateTime($end) : null;
        $this->injury_type = $type;
        $this->severity = $severity;
        $this->expected_return_date = $expected ? new DateTime($expected) : null;
        $this->observations = $obs;
    }

    // Getters
    public function getId(): ?int { return $this->id_injury; }
    public function getPlayerId(): int { return $this->id_player; }
    public function getNamePlayer(): ?string { return $this->name_player; }
    public function getStartDate(): DateTime { return $this->start_date; }
    public function getEndDate(): ?DateTime { return $this->end_date; }
    public function getInjuryType(): ?string { return $this->injury_type; }
    public function getSeverity(): ?string { return $this->severity; }
    public function getExpectedReturn(): ?DateTime { return $this->expected_return_date; }
    public function getObservations(): ?string { return $this->observations; }
}