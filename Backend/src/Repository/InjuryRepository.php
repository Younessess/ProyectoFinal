<?php
namespace App\Repository;

use App\Core\Database;
use App\Model\Injury;
use PDO;

class InjuryRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAllActive(): array {
        $stmt = $this->db->query("SELECT * FROM injuries WHERE end_date IS NULL ORDER BY start_date DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(Injury $i): bool {
        $sql = "INSERT INTO injuries (id_player, start_date, injury_type, severity, expected_return_date, observations) 
                VALUES (:p, :s, :t, :sv, :r, :o)";
        return $this->db->prepare($sql)->execute([
            'p' => $i->getPlayerId(),
            's' => $i->getStartDate()->format('Y-m-d'),
            't' => $i->getInjuryType(),
            'sv' => $i->getSeverity(),
            'r' => $i->getExpectedReturn()?->format('Y-m-d'),
            'o' => $i->getObservations()
        ]);
    }

    public function closeInjury(int $id, string $endDate): bool {
        $sql = "UPDATE injuries SET end_date = :e WHERE id_injury = :id";
        return $this->db->prepare($sql)->execute(['e' => $endDate, 'id' => $id]);
    }
}