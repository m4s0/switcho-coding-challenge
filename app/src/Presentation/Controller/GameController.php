<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\DTO\GameStateDTO;
use App\Application\UseCase\GetGameStatusUseCase;
use App\Application\UseCase\MakeMoveUseCase;
use App\Application\UseCase\StartGameUseCase;
use DomainException;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/game')]
class GameController extends AbstractController
{
    #[Route('/start', name: 'game_start', methods: ['POST'])]
    public function startGame(StartGameUseCase $useCase): JsonResponse
    {
        try {
            $game = $useCase->execute();
            $gameState = GameStateDTO::fromGame($game);

            return $this->json([
                ...$gameState->toArray(),
                'message' => 'New game started',
            ]);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{gameId}/move', name: 'game_move', methods: ['POST'])]
    public function makeMove(int $gameId, Request $request, MakeMoveUseCase $useCase): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['player']) || !isset($data['position'])) {
                return $this->json([
                    'error' => 'Missing required fields: player and position',
                ], Response::HTTP_BAD_REQUEST);
            }

            $gameState = $useCase->execute($gameId, (int) $data['player'], (int) $data['position']);

            return $this->json([
                ...$gameState->toArray(),
                'message' => $this->getGameMessage($gameState),
            ]);
        } catch (DomainException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json([
                'error' => 'Failed to make move: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{gameId}', name: 'game_status', methods: ['GET'])]
    public function getGameStatus(int $gameId, GetGameStatusUseCase $useCase): JsonResponse
    {
        try {
            $gameState = $useCase->execute($gameId);

            return $this->json([
                ...$gameState->toArray(),
            ]);
        } catch (DomainException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json([
                'error' => 'Failed to get game status: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getGameMessage(GameStateDTO $gameState): string
    {
        if ($gameState->isFinished) {
            if ($gameState->winner) {
                return "Player {$gameState->winner} wins!";
            } else {
                return 'Game ended in a draw!';
            }
        }

        return "Player {$gameState->currentPlayer}'s turn";
    }
}
