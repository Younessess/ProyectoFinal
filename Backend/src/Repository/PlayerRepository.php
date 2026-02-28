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
        return new Player(
            (int)$row['id_player'], $row['first_name'], $row['last_name'],
            $row['nickname'], $row['usual_position'], $row['status'],
            new \DateTime($row['created_at'])
        );
    }
}