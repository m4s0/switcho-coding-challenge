<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Repository\GameRepository;

class GameService
{
    public function __construct(
        readonly private GameRepository $gameRepository,
    ) {
    }

    public function createGame(): Game
    {
        $game = new Game();
        $this->gameRepository->save($game, true);

        return $game;
    }

    public function makeMove(Game $game, int $player, int $position): array
    {
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

    private function validateMove(Game $game, int $player, int $position): void
    {
        // Check if game is already finished
        if ($game->isFinished()) {
            throw new \InvalidArgumentException('Game is already finished');
        }

        // Check if player is valid
        if (!in_array($player, [1, 2])) {
            throw new \InvalidArgumentException('Player must be 1 or 2');
        }

        // Check if it's the correct player's turn
        if ($game->getCurrentPlayer() !== $player) {
            throw new \InvalidArgumentException('It is not player '.$player.'\'s turn');
        }

        // Check if position is valid
        if ($position < 0 || $position > 8) {
            throw new \InvalidArgumentException('Position must be between 0 and 8');
        }

        // Check if position is already taken
        $board = $game->getBoard();
        if (null !== $board[$position]) {
            throw new \InvalidArgumentException('Position is already taken');
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
