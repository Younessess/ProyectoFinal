<?php
namespace App\Service;

use App\Repository\MatchRepository;
use App\Model\MatchModel;
use App\DTO\MatchDTO;

class MatchService {
    private MatchRepository $repo;

    public function __construct() {
        $this->repo = new MatchRepository();
    }

    public function getAllMatches(): array {
        $matches = $this->repo->getAll();
        return array_map(function($m) {
            return new MatchDTO(
                $m->getId(),
                $m->getDate()->format('Y-m-d H:i'),
                $m->getOpponent(),
                $m->getCompetition(),
                $m->getVenue(),
                $m->getGoalsFor() ?? 0,
                $m->getGoalsAgainst() ?? 0
            );
        }, $matches);
    }

    /**
     * Crea un partido y devuelve su ID (o null si falla).
     */
    public function createMatch(array $data): ?int {
        // Aquí podrías añadir validaciones (ej: que la fecha sea válida)
        $match = new MatchModel(
            null,
            (int)$data['id_season'],
            $data['date'],
            $data['competition'],
            $data['opponent'],
            $data['venue'],
            (int)($data['goals_for'] ?? 0),
            (int)($data['goals_against'] ?? 0)
        );

        return $this->repo->createMatchAndReturnId($match);
    }
    public function updateMatch(int $id, array $data): bool {
        $matchExists = $this->repo->getById($id);
        if (!$matchExists) {
            return false;
        }
    
        $updatedMatch = new MatchModel(
            $id,
            (int)$data['id_season'],
            $data['date'],
            $data['competition'],
            $data['opponent'],
            $data['venue'],
            (int)($data['goals_for'] ?? 0),
            (int)($data['goals_against'] ?? 0)
        );
    
        return $this->repo->updateMatch($updatedMatch);
    }

    public function getMatchDetails(int $id): ?array {
        return $this->repo->getMatchDetails($id);
    }
}