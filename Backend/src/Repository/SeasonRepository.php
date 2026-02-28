<?php
namespace App\Repository;

use App\Core\Database;
use App\Model\Season;
use PDO;

class SeasonRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM seasons ORDER BY start_date DESC");
        return array_map(fn($row) => $this->mapRow($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?Season {
        $stmt = $this->db->prepare("SELECT * FROM seasons WHERE id_season = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapRow($row) : null;
    }

    public function create(Season $s): bool {
        $sql = "INSERT INTO seasons (name, start_date, end_date, is_active) VALUES (:n, :s, :e, :a)";
        return $this->db->prepare($sql)->execute([
            'n' => $s->getName(),
            's' => $s->getStartDate()?->format('Y-m-d'),
            'e' => $s->getEndDate()?->format('Y-m-d'),
            'a' => $s->isActive() ? 1 : 0
        ]);
    }

    public function update(Season $s): bool {
        $sql = "UPDATE seasons SET name = :n, start_date = :s, end_date = :e, is_active = :a WHERE id_season = :id";
        return $this->db->prepare($sql)->execute([
            'id' => $s->getId(),
            'n' => $s->getName(),
            's' => $s->getStartDate()?->format('Y-m-d'),
            'e' => $s->getEndDate()?->format('Y-m-d'),
            'a' => $s->isActive() ? 1 : 0
        ]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM seasons WHERE id_season = :id")->execute(['id' => $id]);
    }

    private function mapRow(array $row): Season {
        return new Season((int)$row['id_season'], $row['name'], $row['start_date'], $row['end_date'], (int)$row['is_active']);
    }
}