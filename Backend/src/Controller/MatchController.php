<?php
namespace App\Controller;

use App\Service\MatchService;

class MatchController {
    private MatchService $service;

    public function __construct() {
        $this->service = new MatchService();
    }

    public function getAllMatches(): void {
        $dtos = $this->service->getAllMatches();
        $data = array_map(fn($d) => $d->toArray(), $dtos);
        
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function createMatch(): void {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            $this->sendResponse(['error' => 'Invalid JSON'], 400);
            return;
        }

        if ($this->service->createMatch($data)) {
            $this->sendResponse(['message' => 'Match created'], 201);
        } else {
            $this->sendResponse(['error' => 'Could not create match'], 500);
        }
    }

    private function sendResponse(array $data, int $code = 200): void {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data);
    }

    public function updateMatch(int $id): void {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        if (!$data) {
            $this->sendResponse(['error' => 'Invalid JSON'], 400);
            return;
        }
    
        if ($this->service->updateMatch($id, $data)) {
            $this->sendResponse(['message' => 'Match updated successfully']);
        } else {
            $this->sendResponse(['error' => 'Match not found or update failed'], 404);
        }
    }
}