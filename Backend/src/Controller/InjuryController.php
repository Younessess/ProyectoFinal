<?php
namespace App\Controller;

use App\Service\InjuryService;

class InjuryController {
    private InjuryService $service;

    public function __construct() {
        $this->service = new InjuryService();
    }

    public function create() {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->service->registerInjury($data)) {
            header('Content-Type: application/json');
            echo json_encode(['msg' => 'Injury registered and player status updated']);
        }
    }
    public function getActiveInjuries(): void {
        $dtos = $this->service->getActiveInjuries();
        
        // Transformamos los DTOs a array para el JSON [cite: 184]
        $data = array_map(fn($d) => $d->toArray(), $dtos);
        
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    public function close() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validamos que lleguen los IDs y la fecha de cierre 
        if (isset($data['id_injury'], $data['end_date'], $data['id_player'])) {
            $result = $this->service->closeInjury(
                (int)$data['id_injury'], 
                $data['end_date'], 
                (int)$data['id_player']
            );
    
            header('Content-Type: application/json');
            if ($result) {
                echo json_encode(['msg' => 'Injury closed and player is now active']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Could not complete the closure']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields for closing an injury.']);
        }
    }
}