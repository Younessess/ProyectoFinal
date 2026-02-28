<?php
namespace App\Controller;

use App\Service\PlayerService;

class PlayerController {
    private PlayerService $service;

    public function __construct() {
        $this->service = new PlayerService();
    }

    private function jsonResponse($data, $code = 200) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data);
    }

    public function getAllPlayers() { $this->jsonResponse(array_map(fn($p) => $p->toArray(), $this->service->getAllPlayers())); }

    public function getPlayerById($id) {
        $p = $this->service->getPlayerById($id);
        $p ? $this->jsonResponse($p->toArray()) : $this->jsonResponse(['error' => 'Not found'], 404);
    }

    public function createPlayer() {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->service->createPlayer($data) ? $this->jsonResponse(['msg' => 'Created'], 201) : $this->jsonResponse(['error' => 'Fail'], 400);
    }

    public function updatePlayer($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->service->updatePlayer($id, $data) ? $this->jsonResponse(['msg' => 'Updated']) : $this->jsonResponse(['error' => 'Fail'], 400);
    }

    public function deletePlayer($id) {
        $this->service->deletePlayer($id) ? $this->jsonResponse(['msg' => 'Deleted']) : $this->jsonResponse(['error' => 'Fail'], 400);
    }
}