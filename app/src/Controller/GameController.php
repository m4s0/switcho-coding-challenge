<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/game')]
class GameController extends AbstractController
{
    public function __construct(
        readonly private GameService $gameService,
        readonly private GameRepository $gameRepository,
    ) {
    }

    #[Route('/start', name: 'game_start', methods: ['POST'])]
    public function startGame(): JsonResponse
    {
        try {
            $game = $this->gameService->createGame();

            return $this->json([
                'success' => true,
                'game_id' => $game->getId(),
                'board' => $game->getBoardAs2D(),
                'current_player' => $game->getCurrentPlayer(),
                'message' => 'New game started',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to start game: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{gameId}/move', name: 'game_move', methods: ['POST'])]
    public function makeMove(int $gameId, Request $request): JsonResponse
    {
        try {
            // Get game
            $game = $this->gameRepository->find($gameId);
            if (!$game) {
                return $this->json([
                    'success' => false,
                    'error' => 'Game not found',
                ], Response::HTTP_NOT_FOUND);
            }

            // Get request data
            $data = json_decode($request->getContent(), true);

            if (!isset($data['player']) || !isset($data['position'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Missing required fields: player and position',
                ], Response::HTTP_BAD_REQUEST);
            }

            $player = (int) $data['player'];
            $position = (int) $data['position'];

            // Make the move
            $result = $this->gameService->makeMove($game, $player, $position);

            return $this->json([
                'success' => true,
                'game_id' => $gameId,
                'board' => $result['board'],
                'current_player' => $result['current_player'],
                'is_finished' => $result['is_finished'],
                'winner' => $result['winner'],
                'is_draw' => $result['is_draw'],
                'message' => $this->getGameMessage($result),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to make move: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{gameId}', name: 'game_status', methods: ['GET'])]
    public function getGameStatus(int $gameId): JsonResponse
    {
        try {
            $game = $this->gameRepository->find($gameId);
            if (!$game) {
                return $this->json([
                    'success' => false,
                    'error' => 'Game not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'success' => true,
                'game_id' => $gameId,
                'board' => $game->getBoardAs2D(),
                'current_player' => $game->getCurrentPlayer(),
                'is_finished' => $game->isFinished(),
                'winner' => $game->getWinner(),
                'created_at' => $game->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to get game status: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getGameMessage(array $result): string
    {
        if ($result['is_finished']) {
            if ($result['winner']) {
                return "Player {$result['winner']} wins!";
            } else {
                return 'Game ended in a draw!';
            }
        }

        return "Player {$result['current_player']}'s turn";
    }
}
