<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 28/05/2016
 * Time: 3:26 PM
 */

namespace GroupBot\Brains\Minesweeper;


use GroupBot\Brains\Minesweeper\Types\Board;
use GroupBot\Brains\Minesweeper\Types\Game;
use GroupBot\Types\Chat;

class Minesweeper
{
    /** @var  Game */
    public $game;

    /** @var  Chat */
    public $chat;

    /** @var  SQL */
    public $SQL;

    public function __construct(\PDO $db, Chat $chat)
    {
        $this->SQL = new SQL($db);
        $this->chat = $chat;
        $this->loadOrCreateGame();
    }

    private function newGame()
    {
        $board = new Board();
        $board->width = 8;
        $board->height = 12;
        $board->generateNewBoard(15);
        $game = new Game($board, $this->chat->id, null);
        return $game;
    }

    private function loadOrCreateGame()
    {
        if (!$this->game = $this->SQL->select_game($this->chat->id)) {
            $this->game = $this->newGame();
            $this->SQL->insert_game($this->game);
        }
    }

    public function saveGame()
    {
        return $this->SQL->update_game($this->game);
    }

    public function endGame()
    {
        return $this->SQL->delete_game($this->game);
    }

    public function revealTile($x, $y)
    {
        if ($tile = $this->game->board->getTile($x, $y)) {
            $tile->revealed = true;

            if (!$tile->mine && $tile->number == 0) {
                $this->game->board->revealAdjacentEmptyTiles($x, $y);
            }

            if ($tile->mine) {
                
            }

            $this->saveGame();
            return true;
        }
        return false;
    }
    
    public function flagTile($x, $y)
    {
        if ($tile = $this->game->board->getTile($x, $y)) {
            $tile->flagged = true;
            $this->saveGame();
            return true;
        }
        return false;
    }
    
    public function getBoard($reveal = false)
    {
        return $this->game->board->getBoardTelegramKeyboard($reveal);
    }
}