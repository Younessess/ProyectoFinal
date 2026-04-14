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

    /**
     * Crea un partido y devuelve su ID generado, o null en caso de error.
     */
    public function createMatchAndReturnId(MatchModel $match): ?int {
        $sql = "INSERT INTO matches (id_season, date, competition, opponent, venue, goals_for, goals_against) 
                VALUES (:id_season, :date, :competition, :opponent, :venue, :goals_for, :goals_against)";

        $stmt = $this->db->prepare($sql);

        $ok = $stmt->execute([
            'id_season'     => $match->getSeasonId(),
            'date'          => $match->getDate()->format('Y-m-d H:i:s'),
            'competition'   => $match->getCompetition(),
            'opponent'      => $match->getOpponent(),
            'venue'         => $match->getVenue(),
            'goals_for'     => $match->getGoalsFor(),
            'goals_against' => $match->getGoalsAgainst()
        ]);

        if (!$ok) {
            return null;
        }

        return (int)$this->db->lastInsertId();
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

    /**
     * Devuelve los detalles de un partido junto con los jugadores 
     * listados de mayor a menor puntuación final.
     */
    public function getMatchDetails(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT m.*, s.name as season_name 
            FROM matches m 
            LEFT JOIN seasons s ON s.id_season = m.id_season
            WHERE m.id_match = :id
        ");
        $stmt->execute(['id' => $id]);
        $match = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$match) {
            return null;
        }

        $sql = "
            SELECT 
                p.id_player,
                p.first_name,
                p.last_name,
                p.usual_position,
                s.evaluated_position,
                s.attack_score,
                s.build_up_score,
                s.defense_score,
                s.minutes_factor,
                s.final_score,
                s.positive_feedback,
                s.negative_feedback,
                pms.minutes_played,
                pms.goals,
                pms.assists
            FROM scores s
            JOIN players p ON p.id_player = s.id_player
            LEFT JOIN player_match_stats pms ON pms.id_match = s.id_match AND pms.id_player = s.id_player
            WHERE s.id_match = :id
            ORDER BY s.final_score DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'match' => $match,
            'players' => $players
        ];
    }
}