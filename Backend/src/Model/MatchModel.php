<?php
namespace App\Model;

use DateTime;

class MatchModel {
    private ?int $id_match;
    private ?int $id_season;
    private DateTime $date;
    private string $competition;
    private string $opponent;
    private string $venue; // 'home' o 'away'
    private ?int $goals_for;
    private ?int $goals_against;
    private DateTime $created_at;

    public function __construct(?int $id, ?int $season, string $date, string $comp, string $opp, string $venue, ?int $gf = 0, ?int $ga = 0, ?string $created = null) {
        $this->id_match = $id;
        $this->id_season = $season;
        $this->date = new DateTime($date);
        $this->competition = $comp;
        $this->opponent = $opp;
        $this->venue = $venue;
        $this->goals_for = $gf;
        $this->goals_against = $ga;
        $this->created_at = $created ? new DateTime($created) : new DateTime();
    }

    // Getters
    public function getId(): ?int { return $this->id_match; }
    public function getSeasonId(): ?int { return $this->id_season; }
    public function getDate(): DateTime { return $this->date; }
    public function getCompetition(): string { return $this->competition; }
    public function getOpponent(): string { return $this->opponent; }
    public function getVenue(): string { return $this->venue; }
    public function getGoalsFor(): ?int { return $this->goals_for; }
    public function getGoalsAgainst(): ?int { return $this->goals_against; }
}