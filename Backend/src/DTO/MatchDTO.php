<?php
namespace App\DTO;

class MatchDTO {
    public int $id;
    public string $date;
    public string $opponent;
    public string $competition;
    public string $venue;
    public string $result;

    public function __construct(int $id, string $date, string $opp, string $comp, string $venue, int $gf, int $ga) {
        $this->id = $id;
        $this->date = $date;
        $this->opponent = $opp;
        $this->competition = $comp;
        $this->venue = $venue;
        $this->result = "$gf - $ga";
    }

    public function toArray(): array {
        return [
            'id_match' => $this->id,
            'date' => $this->date,
            'opponent' => $this->opponent,
            'competition' => $this->competition,
            'venue' => $this->venue,
            'result' => $this->result
        ];
    }
}