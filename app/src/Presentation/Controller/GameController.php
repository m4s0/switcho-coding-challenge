<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\DTO\GameStateDTO;
use App\Application\UseCase\GetGameStatusUseCase;
use App\Application\UseCase\MakeMoveUseCase;
use App\Application\UseCase\StartGameUseCase;
use App\Domain\Exception\DomainException;
use App\Domain\Exception\NotFoundException;
use App\Domain\Service\MinimaxStrategy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/game')]
class GameController extends AbstractController
{
    public function __construct(
        private readonly StartGameUseCase $startGameUseCase,
        private readonly MakeMoveUseCase $makeMoveUseCase,
        private readonly GetGameStatusUseCase $getGameStatusUseCase,
        private readonly MinimaxStrategy $minimaxStrategy,
    ) {
    }

    #[Route('/start', name: 'game_start', methods: ['POST'])]
    public function startGame(): JsonResponse
    {
        try {
            $game = $this->startGameUseCase->execute();
            $gameState = GameStateDTO::fromGame($game);

            return $this->json($gameState, Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{gameId}/move', name: 'game_move', methods: ['POST'])]
    public function makeMove(string $gameId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            if (!isset($data['player'], $data['row'], $data['col'])) {
                return $this->json(
                    ['error' => 'Missing required fields: player, row, col'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $game = $this->makeMoveUseCase->execute(
                $gameId,
                (int) $data['player'],
                (int) $data['row'],
                (int) $data['col']
            );

            $opponentWinningMoves = $this->minimaxStrategy->findOpponentWinningMoves($game->getBoard(), $game->getCurrentPlayer());
            $bestMove = $this->minimaxStrategy->findBestMove($game->getBoard(), $game->getCurrentPlayer());

            $gameState = GameStateDTO::fromGame($game, $opponentWinningMoves, $bestMove);

            return $this->json($gameState, Response::HTTP_CREATED);
        } catch (NotFoundException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (DomainException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'Internal server error: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{gameId}/status', name: 'game_status', methods: ['GET'])]
    public function getGameStatus(string $gameId): JsonResponse
    {
        try {
            $game = $this->getGameStatusUseCase->execute($gameId);
            $gameState = GameStateDTO::fromGame($game);

            return $this->json($gameState);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'Internal server error: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
