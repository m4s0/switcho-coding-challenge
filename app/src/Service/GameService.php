<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Uid\Uuid;

class GameService
{
    public function __construct(
        readonly private GameRepository $gameRepository,
        readonly private EntityManager $entityManager,
    ) {
    }

    public function createGame(): Game
    {
        $game = new Game();

        try {
            $this->entityManager->persist($game);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            throw new \RuntimeException('Failed to create game: '.$e->getMessage());
        }

        return $game;
    }

    public function makeMove(string $gameId, int $player, int $row, int $col): Game
    {
        $game = $this->gameRepository->findById(Uuid::fromString($gameId));
        if (!$game) {
            throw new \LogicException('Game not found');
        }

        $this->validate($player, $row, $col, $game);

        $moveSuccess = $game->makeMove($player, $row, $col);

        if (!$moveSuccess) {
            throw new \RuntimeException('Failed to move game: '.$gameId);
        }

        try {
            $this->entityManager->flush();

            return $game;
        } catch (OptimisticLockException $e) {
            throw new \RuntimeException('Failed to save game state: '.$e->getMessage());
        } catch (ORMException $e) {
            throw new \RuntimeException('Failed to save game state: '.$e->getMessage());
        }

        // Validate the move
        $this->validateMove($game, $player, $position);

        // Make the move
        $board = $game->getBoard();
        $board[$position] = $player;
        $game->setBoard($board);

        // Check for winner or draw
        $winner = $this->checkWinner($board);
        $isDraw = $this->checkDraw($board);

        if (null !== $winner) {
            $game->setWinner($winner);
            $game->setIsFinished(true);
        } elseif ($isDraw) {
            $game->setIsFinished(true);
        } else {
            // Switch to next player
            $game->setCurrentPlayer(1 === $player ? 2 : 1);
        }

        $this->gameRepository->save($game, true);

        return [
            'board' => $game->getBoardAs2D(),
            'board_1d' => $game->getBoard(),
            'current_player' => $game->getCurrentPlayer(),
            'is_finished' => $game->isFinished(),
            'winner' => $game->getWinner(),
            'is_draw' => $isDraw && null === $winner,
        ];
    }

    private function validate(int $player, int $row, int $col, Game $game): void
    {
        if (!in_array($player, [1, 2])) {
            throw new \LogicException('Invalid player number. Must be 1 or 2.');
        }
        if ($row < 0 || $row > 2 || $col < 0 || $col > 2) {
            throw new \LogicException('Invalid position. Row and column must be between 0 and 2.');
        }
        if ('ongoing' !== $game->getStatus()) {
            throw new \LogicException('Game is already over. Current status: '.$game->getStatus());
        }
        if ($game->getCurrentPlayer() !== $player) {
            throw new \LogicException('It is not your turn.');
        }
        if (!$game->isMoveValid($row, $col)) {
            throw new \LogicException('Invalid move. Position might be taken or game is over.');
        }
    }

    private function checkWinner(array $board): ?int
    {
        // Define winning combinations (indices in 1D array)
        $winningCombinations = [
            [0, 1, 2], [3, 4, 5], [6, 7, 8], // rows
            [0, 3, 6], [1, 4, 7], [2, 5, 8], // columns
            [0, 4, 8], [2, 4, 6],             // diagonals
        ];

        foreach ($winningCombinations as $combination) {
            [$a, $b, $c] = $combination;

            if (null !== $board[$a]
                && $board[$a] === $board[$b]
                && $board[$b] === $board[$c]) {
                return $board[$a];
            }
        }

        return null;
    }

    private function checkDraw(array $board): bool
    {
        return !in_array(null, $board, true);
    }
}
