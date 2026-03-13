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

    /**
     * Devuelve la estructura completa para la ficha del jugador:
     * - player: datos básicos + dorsal actual
     * - matches: historial de partidos jugados
     * - scores_by_position: medias por posición evaluada
     * - injuries: lesión activa (si existe) + historial
     */
    public function getPlayerDetails(int $id): ?array {
        $rows = $this->repo->getDetails($id);

        // Si no hay filas, comprobamos si el jugador existe al menos.
        if (!$rows) {
            $player = $this->repo->getById($id);
            if (!$player) {
                return null;
            }

            return [
                'player' => $this->buildPlayerBasicArrayFromModel($player),
                'matches' => [],
                'scores_by_position' => [],
                'injuries' => [
                    'active' => null,
                    'history' => [],
                ],
            ];
        }

        $first = $rows[0];

        $playerBasic = [
            'id_player'      => (int)$first['id_player'],
            'first_name'     => $first['first_name'],
            'last_name'      => $first['last_name'],
            'nickname'       => $first['nickname'],
            'usual_position' => $first['usual_position'],
            'status'         => $first['status'],
            'created_at'     => $first['created_at'],
            'squad_number'   => $first['squad_number'] !== null ? (int)$first['squad_number'] : null,
            'season_name'    => $first['season_name'] ?? null,
        ];

        $matches = [];
        $scoresAgg = [];
        $injuryHistory = [];
        $activeInjury = null;
        $today = new \DateTimeImmutable('today');

        foreach ($rows as $row) {
            // Historial de partidos jugados
            if (!empty($row['id_match'])) {
                $matchId = (int)$row['id_match'];
                if (!isset($matches[$matchId])) {
                    $matches[$matchId] = [
                        'id_match'       => $matchId,
                        'date'           => $row['match_date'],
                        'competition'    => $row['competition'],
                        'opponent'       => $row['opponent'],
                        'venue'          => $row['venue'],
                        'minutes_played' => $row['minutes_played'],
                        'final_score'    => $row['final_score'],
                    ];
                }
            }

            // Puntuaciones por posición evaluada (para medias)
            if (!empty($row['evaluated_position'])) {
                $pos = $row['evaluated_position'];
                if (!isset($scoresAgg[$pos])) {
                    $scoresAgg[$pos] = [
                        'evaluated_position' => $pos,
                        'matches_count'      => 0,
                        'final_score_sum'    => 0.0,
                        'attack_score_sum'   => 0.0,
                        'build_up_score_sum' => 0.0,
                        'defense_score_sum'  => 0.0,
                    ];
                }

                $scoresAgg[$pos]['matches_count']++;
                $scoresAgg[$pos]['final_score_sum'] += (float)$row['final_score'];
                $scoresAgg[$pos]['attack_score_sum'] += (float)$row['attack_score'];
                $scoresAgg[$pos]['build_up_score_sum'] += (float)$row['build_up_score'];
                $scoresAgg[$pos]['defense_score_sum'] += (float)$row['defense_score'];
            }

            // Lesiones: activa + historial completo
            if (!empty($row['id_injury'])) {
                $injuryId = (int)$row['id_injury'];
                if (!isset($injuryHistory[$injuryId])) {
                    $injury = [
                        'id_injury'           => $injuryId,
                        'start_date'          => $row['injury_start_date'],
                        'end_date'            => $row['injury_end_date'],
                        'injury_type'         => $row['injury_type'],
                        'severity'            => $row['severity'],
                        'expected_return_date'=> $row['expected_return_date'],
                    ];

                    $injuryHistory[$injuryId] = $injury;

                    // Consideramos lesión activa si no tiene fecha de fin
                    // o la fecha de fin es posterior o igual a hoy.
                    if ($row['injury_end_date'] === null) {
                        $activeInjury = $injury;
                    } else {
                        try {
                            $endDate = new \DateTimeImmutable($row['injury_end_date']);
                            if ($endDate >= $today) {
                                $activeInjury = $injury;
                            }
                        } catch (\Exception) {
                            // Si la fecha es inválida, la ignoramos para el cálculo de activa.
                        }
                    }
                }
            }
        }

        // Transformamos las agregaciones de puntuación en medias
        $scoresByPosition = [];
        foreach ($scoresAgg as $pos => $data) {
            $count = max(1, $data['matches_count']);
            $scoresByPosition[] = [
                'evaluated_position' => $pos,
                'matches_count'      => $data['matches_count'],
                'avg_final_score'    => $data['final_score_sum'] / $count,
                'avg_attack_score'   => $data['attack_score_sum'] / $count,
                'avg_build_up_score' => $data['build_up_score_sum'] / $count,
                'avg_defense_score'  => $data['defense_score_sum'] / $count,
            ];
        }

        return [
            'player' => $playerBasic,
            'matches' => array_values($matches),
            'scores_by_position' => $scoresByPosition,
            'injuries' => [
                'active' => $activeInjury,
                'history' => array_values($injuryHistory),
            ],
        ];
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
        return new PlayerDTO($p->getId(), $p->getFirstName(), $p->getLastName(), $p->getNickname(), $p->getUsualPosition(), $p->getStatus(), $p->getSquadNumber());
    }

    private function buildPlayerBasicArrayFromModel(Player $p): array {
        return [
            'id_player'      => $p->getId(),
            'first_name'     => $p->getFirstName(),
            'last_name'      => $p->getLastName(),
            'nickname'       => $p->getNickname(),
            'usual_position' => $p->getUsualPosition(),
            'status'         => $p->getStatus(),
            'created_at'     => $p->getCreatedAt()->format('Y-m-d H:i:s'),
            'squad_number'   => $p->getSquadNumber(),
            'season_name'    => null,
        ];
    }
}