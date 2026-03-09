<?php
namespace App\Repository;

use App\Core\Database;
use App\Model\Player;
use PDO;

class PlayerRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM players ORDER BY last_name ASC");
        return array_map(fn($row) => $this->mapRow($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?Player {
        $stmt = $this->db->prepare("SELECT * FROM players WHERE id_player = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapRow($row) : null;
    }

    /**
     * Devuelve toda la información necesaria para la ficha de jugador:
     * - Datos básicos del jugador y dorsal actual en la temporada activa
     * - Historial de partidos jugados
     * - Puntuaciones por partido y posición evaluada
     * - Lesiones (activas e histórico)
     */
    public function getDetails(int $id): array {
        $sql = "
            SELECT
                p.id_player,
                p.first_name,
                p.last_name,
                p.nickname,
                p.usual_position,
                p.status,
                p.created_at,
                psn.squad_number,
                s.name AS season_name,
                m.id_match,
                m.date AS match_date,
                m.competition,
                m.opponent,
                m.venue,
                pms.minutes_played,
                sc.id_score,
                sc.evaluated_position,
                sc.attack_score,
                sc.build_up_score,
                sc.defense_score,
                sc.final_score,
                inj.id_injury,
                inj.start_date AS injury_start_date,
                inj.end_date AS injury_end_date,
                inj.injury_type,
                inj.severity,
                inj.expected_return_date
            FROM players p
            LEFT JOIN player_squad_numbers psn
                ON psn.id_player = p.id_player
                AND psn.id_season = (
                    SELECT id_season
                    FROM seasons
                    WHERE is_active = 1
                    LIMIT 1
                )
            LEFT JOIN seasons s ON s.id_season = psn.id_season
            LEFT JOIN player_match_stats pms ON pms.id_player = p.id_player
            LEFT JOIN matches m ON m.id_match = pms.id_match
            LEFT JOIN scores sc
                ON sc.id_match = m.id_match
                AND sc.id_player = p.id_player
            LEFT JOIN injuries inj ON inj.id_player = p.id_player
            WHERE p.id_player = :id
            ORDER BY m.date DESC, sc.id_score DESC, inj.start_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(Player $p): bool {
        $sql = "INSERT INTO players (first_name, last_name, nickname, usual_position, status) 
                VALUES (:f, :l, :n, :p, :s)";
        return $this->db->prepare($sql)->execute([
            'f' => $p->getFirstName(), 'l' => $p->getLastName(),
            'n' => $p->getNickname(), 'p' => $p->getUsualPosition(), 's' => $p->getStatus()
        ]);
    }

    public function update(Player $p): bool {
        $sql = "UPDATE players SET first_name = :f, last_name = :l, nickname = :n, 
                usual_position = :pos, status = :s WHERE id_player = :id";
        return $this->db->prepare($sql)->execute([
            'id' => $p->getId(), 'f' => $p->getFirstName(), 'l' => $p->getLastName(),
            'n' => $p->getNickname(), 'pos' => $p->getUsualPosition(), 's' => $p->getStatus()
        ]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM players WHERE id_player = :id")->execute(['id' => $id]);
    }

    private function mapRow(array $row): Player {
        $player = new Player(
            (int)$row['id_player'], $row['first_name'], $row['last_name'],
            $row['nickname'], $row['usual_position'], $row['status'],
            new \DateTime($row['created_at'])
        );
        if (isset($row['squad_number'])) {
            $player->setSquadNumber((int)$row['squad_number']);
        }
        return $player;
    }
}