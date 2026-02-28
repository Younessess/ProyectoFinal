<?php
namespace App\Service;

use App\Repository\SeasonRepository;
use App\Model\Season;
use App\DTO\SeasonDTO;

class SeasonService {
    private SeasonRepository $repo;

    public function __construct() {
        $this->repo = new SeasonRepository();
    }

    public function listAll(): array {
        return array_map(function($s) {
            return new SeasonDTO(
                $s->getId(), $s->getName(),
                $s->getStartDate()?->format('Y-m-d'),
                $s->getEndDate()?->format('Y-m-d'),
                $s->isActive()
            );
        }, $this->repo->getAll());
    }

    public function save(array $data): bool {
        $s = new Season(null, $data['name'], $data['start_date'], $data['end_date'], $data['is_active'] ?? 0);
        return $this->repo->create($s);
    }

    public function update(int $id, array $data): bool {
        if (!$this->repo->getById($id)) return false;
        $s = new Season($id, $data['name'], $data['start_date'], $data['end_date'], $data['is_active'] ?? 0);
        return $this->repo->update($s);
    }

    public function remove(int $id): bool {
        return $this->repo->delete($id);
    }
}