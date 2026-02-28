<?php
namespace App\Repository;

use App\Core\Database;
use App\Model\MatchModel;
use PDO;

class MatchRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM matches ORDER BY date DESC");
        return array_map(fn($row) => $this->mapRow($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?MatchModel {
        $stmt = $this->db->prepare("SELECT * FROM matches WHERE id_match = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapRow($row) : null;
    }

    private function mapRow(array $row): MatchModel {
        return new MatchModel(
            (int)$row['id_match'], (int)$row['id_season'], $row['date'],
            $row['competition'], $row['opponent'], $row['venue'],
            (int)$row['goals_for'], (int)$row['goals_against'], $row['created_at']
        );
    }
    public function createMatch(MatchModel $match): bool {
        $sql = "INSERT INTO matches (id_season, date, competition, opponent, venue, goals_for, goals_against) 
                VALUES (:id_season, :date, :competition, :opponent, :venue, :goals_for, :goals_against)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id_season'     => $match->getSeasonId(),
            'date'          => $match->getDate()->format('Y-m-d H:i:s'),
            'competition'   => $match->getCompetition(),
            'opponent'      => $match->getOpponent(),
            'venue'         => $match->getVenue(),
            'goals_for'     => $match->getGoalsFor(),
            'goals_against' => $match->getGoalsAgainst()
        ]);
    }

    public function updateMatch(MatchModel $match): bool {
        $sql = "UPDATE matches SET 
                    id_season = :id_season, 
                    date = :date, 
                    competition = :competition, 
                    opponent = :opponent, 
                    venue = :venue, 
                    goals_for = :goals_for, 
                    goals_against = :goals_against 
                WHERE id_match = :id_match";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id_match'      => $match->getId(),
            'id_season'     => $match->getSeasonId(),
            'date'          => $match->getDate()->format('Y-m-d H:i:s'),
            'competition'   => $match->getCompetition(),
            'opponent'      => $match->getOpponent(),
            'venue'         => $match->getVenue(),
            'goals_for'     => $match->getGoalsFor(),
            'goals_against' => $match->getGoalsAgainst()
        ]);
    }
}