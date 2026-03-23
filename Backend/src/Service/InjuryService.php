<?php
namespace App\Service;

use App\Repository\InjuryRepository;
use App\Repository\PlayerRepository;
use App\Model\Injury;
use App\DTO\InjuryDTO;

class InjuryService {
    private InjuryRepository $injuryRepo;
    private PlayerRepository $playerRepo;

    public function __construct() {
        $this->injuryRepo = new InjuryRepository();
        $this->playerRepo = new PlayerRepository();
    }

    public function registerInjury(array $data): bool {
        $injury = new Injury(
            null, 
            $data['id_player'], 
            null, // name_player (not needed for creation)
            $data['start_date'], 
            null, // end_date
            $data['injury_type'], 
            $data['severity'], 
            $data['expected_return_date'] ?? null, 
            $data['observations'] ?? null
        );

        if ($this->injuryRepo->create($injury)) {
            // Tarea DAW: Actualizar estado del jugador a 'lesionado' [cite: 115]
            return $this->playerRepo->updateStatus($data['id_player'], 'injured');
        }
        return false;
    }
    public function getActiveInjuries(): array {
        $results = $this->injuryRepo->getAllActive();
        return array_map(function($row) {
            return new \App\DTO\InjuryDTO(
                $row['id_injury'],
                $row['id_player'],
                $row['name_player'],
                $row['start_date'],
                $row['end_date'],
                $row['injury_type'],
                $row['severity'],
                $row['expected_return_date'],
                $row['observations']
            );
        }, $results);
    }
    public function closeInjury(int $id_injury, string $endDate, int $id_player): bool {
        // 1. Usamos el nombre exacto del método del Repository
        $success = $this->injuryRepo->closeInjury($id_injury, $endDate);
    
        if ($success) {
            //: Actualizar el estado del jugador a 'active' 
            return $this->playerRepo->updateStatus($id_player, 'active');
        }
        
        return false;
    }
}