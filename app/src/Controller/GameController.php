<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/games')]
class GameController extends AbstractController
{
    public function __construct(
        readonly private GameService $gameService,
    ) {
    }

    #[Route('/start', name: 'game_start', methods: ['POST'])]
    public function startGame(): JsonResponse
    {
        try {
            $game = $this->gameService->createGame();

            return $this->json(
                ['gameId' => $game->getId()], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{gameId}/move', name: 'game_move', methods: ['POST'])]
    public function makeMove(string $gameId, Request $request): JsonResponse
    {
        if (!Uuid::isValid($gameId)) {
            return $this->json(['error' => 'Invalid Game ID format.'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['player'], $data['row'], $data['col'])) {
            return $this->json(['error' => 'Missing parameters: player, row, or col.'], Response::HTTP_BAD_REQUEST);
        }

        $player = (int) $data['player'];
        $row = (int) $data['row'];
        $col = (int) $data['col'];

        try {
            $game = $this->gameService->makeMove($gameId, $player, $row, $col);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        // --- Prepare Response ---
        $winner = $game->getWinner();
        $hasWon = null !== $winner;

        return $this->json([
            'board' => $game->getBoard(),
            'hasWon' => $hasWon,
            'winner' => $winner,
            'status' => $game->getStatus(),
            'nextPlayer' => 'ongoing' === $game->getStatus() ? $game->getCurrentPlayer() : null,
        ]);
    }

    #[Route('/{id}', name: 'game_status', methods: ['GET'])]
    public function getGameStatus(string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return $this->json(['error' => 'Invalid Game ID format.'], Response::HTTP_BAD_REQUEST);
        }

        $game = $this->entityManager->getRepository(Game::class)->find(Uuid::fromString($id));

        if (!$game) {
            return $this->json(['error' => 'Game not found.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'gameId' => $game->getId(),
            'board' => $game->getBoard(),
            'currentPlayer' => $game->getCurrentPlayer(),
            'winner' => $game->getWinner(),
            'status' => $game->getStatus(),
        ]);
    }
}
