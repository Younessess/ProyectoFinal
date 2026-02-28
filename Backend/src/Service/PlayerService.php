<?php
namespace App\Service;

use App\Repository\PlayerRepository;
use App\Model\Player;
use App\DTO\PlayerDTO;

class PlayerService {
    private PlayerRepository $repo;

    public function __construct() {
        $this->repo = new PlayerRepository();
    }

    public function getAllPlayers(): array {
        return array_map(fn($p) => $this->mapToDTO($p), $this->repo->getAll());
    }

    public function getPlayerById(int $id): ?PlayerDTO {
        $p = $this->repo->getById($id);
        return $p ? $this->mapToDTO($p) : null;
    }

    public function createPlayer(array $data): bool {
        $p = new Player(null, $data['first_name'], $data['last_name'], $data['nickname'], $data['usual_position'], $data['status'] ?? 'active');
        return $this->repo->create($p);
    }

    public function updatePlayer(int $id, array $data): bool {
        $p = $this->repo->getById($id);
        if (!$p) return false;
        $p->setFirstName($data['first_name']);
        $p->setLastName($data['last_name']);
        $p->setNickname($data['nickname']);
        $p->setUsualPosition($data['usual_position']);
        $p->setStatus($data['status']);
        return $this->repo->update($p);
    }

    public function deletePlayer(int $id): bool {
        return $this->repo->delete($id);
    }

    private function mapToDTO(Player $p): PlayerDTO {
        return new PlayerDTO($p->getId(), $p->getFirstName(), $p->getLastName(), $p->getNickname(), $p->getUsualPosition(), $p->getStatus());
    }
}