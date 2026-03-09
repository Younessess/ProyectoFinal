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

        $id = $this->service->createMatch($data);
        if ($id !== null) {
            $this->sendResponse(['message' => 'Match created', 'id_match' => $id], 201);
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

    /**
     * Importa un archivo Excel con estadísticas de un partido, sin procesarlo.
     * Solo guarda el fichero en el servidor asociado al partido.
     */
    public function importMatchStats(int $id): void {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->sendResponse(['error' => 'File upload failed'], 400);
            return;
        }

        $tmpName = $_FILES['file']['tmp_name'];
        $originalName = basename($_FILES['file']['name']);

        $uploadDir = __DIR__ . '/../../uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $safeName = 'match_' . $id . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $originalName);
        $targetPath = $uploadDir . '/' . $safeName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            $this->sendResponse(['error' => 'Could not save uploaded file'], 500);
            return;
        }

        // Más adelante aquí se podrían insertar las filas en player_match_stats usando este archivo.

        $this->sendResponse([
            'message' => 'File imported successfully',
            'filename' => $safeName
        ], 201);
    }
}