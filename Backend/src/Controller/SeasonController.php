<?php
namespace App\Controller;

use App\Service\SeasonService;

class SeasonController {
    private SeasonService $service;

    public function __construct() {
        $this->service = new SeasonService();
    }

    public function getAll() {
        $res = array_map(fn($dto) => $dto->toArray(), $this->service->listAll());
        $this->send($res);
    }

    public function create() {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->service->save($data) ? $this->send(['msg' => 'OK'], 201) : $this->send(['err' => 'Error'], 400);
    }

    public function update($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->service->update($id, $data) ? $this->send(['msg' => 'Updated']) : $this->send(['err' => 'Error'], 400);
    }

    public function delete($id) {
        $this->service->remove($id) ? $this->send(['msg' => 'Deleted']) : $this->send(['err' => 'Error'], 400);
    }

    private function send($data, $code = 200) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data);
    }
}