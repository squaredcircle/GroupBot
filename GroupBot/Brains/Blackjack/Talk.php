<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 14/12/2015
 * Time: 10:26 PM
 */

namespace GroupBot\Brains\Blackjack;


use GroupBot\Brains\Blackjack\Enums\PlayerState;
use GroupBot\Brains\Blackjack\Types\Game;
use GroupBot\Brains\Blackjack\Types\Player;
use GroupBot\Command\misc\emoji;
use GroupBot\Types\Chat;

class Talk extends \GroupBot\Brains\CardGame\Talk
{
    /** @var Chat */
    private $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    public function turn_expired(\GroupBot\Brains\CardGame\Types\Player $player)
    {
        $this->addMessage(emoji(0x1F4E2) . " A game is in progress."
            . "\n" . emoji(0x231B) . " " . $player->user->getPrefixedUserName() . " hasn't made a move in over 5 minutes. They will automatically stand.");
    }

    /**
     * @param Game $game
     */
    public function pre_game_status(\GroupBot\Brains\CardGame\Types\Game $game)
    {
        $out = emoji(0x1F4E2) . " Waiting for players to join the game.\nCurrent players: ";
        foreach ($game->Players as $player) {
            $out .= "*" . $player->user->getName() . "*, ";
        }
        $out = substr($out, 0, -2);
        $out .= "\nOther players can join with /blackjack, or you can start the game with /bjstart";
        $this->addMessage($out);
    }

    /**
     * @param Game $game
     */
    public function game_status(\GroupBot\Brains\CardGame\Types\Game $game)
    {
        $out = emoji(0x1F4E2) . " A game is in progress. The table is set as follows:";
        $out .= "\n🃏 Dealer: " . $game->Dealer->Hand->getHandString();
        foreach ($game->Players as $player) {
            switch ($player->State) {
                case PlayerState::BlackJack:
                    $state = "Blackjack";
                    break;
                case PlayerState::Bust:
                    $state = "Bust";
                    break;
                case PlayerState::Hit:
                    $state = "Hit";
                    break;
                case PlayerState::Stand:
                    $state = "Stand";
                    break;
                case PlayerState::Surrender:
                    $state = "Surrender";
                    break;
                case PlayerState::TwentyOne:
                    $state = "Twenty one";
                    break;
                case PlayerState::Join:
                    $state = "Waiting";
                    break;
                default:
                    $state = "";
                    break;
            }
            $out .= "\n🃏 *" . $player->user->getName() . "*: " . $player->Hand->getHandString() . " _(" . $state . ", " . emoji(0x1F4B0) . "_`" . $player->bet . "`_)_";
        }
        $out .= "\n" . emoji(0x1F449) . "It is now " . $game->getCurrentPlayer()->user->getName() . "'s turn.";
        $this->addMessage($out);
    }

    public function join_game(\GroupBot\Brains\CardGame\Types\Player $player)
    {
        $out = emoji(0x1F4B0) . " *" . $player->user->getName() . "* has joined the game";
        if ($player->bet > 0) {
            $out .= " with a bet of " . $player->bet . " coin.";
        } else {
            $out .= ".";
        }
        $out .= "\nOthers can join the game with the buttons below.";

        $this->addMessage($out);

        $this->keyboard =
            [
                [
                    [
                        'text' => emoji(0x1F4B5) . " Join game - default bet",
                        'callback_data' => '/blackjack'
                    ],
                    [
                        'text' => emoji(0x1F4B0) . " Join game - bet double",
                        'callback_data' => '/blackjack ' . 2 * $player->bet
                    ],
                ],
                [
                    [
                        'text' => emoji(0x1F4B0) . " Join game - bet half",
                        'callback_data' => '/blackjack all/2'
                    ],
                    [
                        'text' => emoji(0x1F4B0) . " Join game - bet all",
                        'callback_data' => '/blackjack all'
                    ]
                ],
                [
                    [
                        'text' => emoji(0x1F0CF) . ' Start game',
                        'callback_data' => '/bjstart'
                    ]
                ]
            ];
    }

    /**
     * @param Game $Game
     */
    public function start_game(\GroupBot\Brains\CardGame\Types\Game $Game)
    {
        if ($Game->getNumberOfPlayers() > 1) {
            $this->addMessage("💬 The game begins with " . $Game->getNumberOfPlayers() . " players.");
        }

        $this->addMessage("🃏 The dealer draws " . $Game->Dealer->Hand->getHandString() . " (" . $Game->Dealer->Hand->Value . ")");
        foreach ($Game->Players as $Player) {
            $this->addMessage("🃏 *" . $Player->user->getName() . "* has " . $Player->Hand->getHandString() . " (" . $Player->Hand->Value . ")");
        }
        foreach ($Game->Players as $Player) {
            if ($Player->Hand->isBlackjack()) {
                $this->addMessage("✌ *" . $Player->user->getName() . "* has blackjack! They stand.");
                $Player->no_blackjacks++;
            }
        }

        if (!$Game->areAllPlayersDone()) {
            if ($Game->getNumberOfPlayers() > 1) {
                $this->addMessage("💬 *" . $Game->getCurrentPlayer()->user->getName() . "* goes first.");
            } else {
                $this->addMessage("👉 Please place your move.");
            }
            $this->next_turn_options($Game);
        }
    }

    public function stand(Player $player)
    {
        $this->addMessage("👌 *" . $player->user->getName() . "* stands.");
    }

    public function blackjack(Player $player)
    {
        $this->addMessage("✌ *" . $player->user->getName() . "* has blackjack!");
    }

    /**
     * @param Player $Player
     */
    private function next_turn_options(Game $game)
    {
        /** @var Player $Player */
        $Player = $game->getCurrentPlayer();

        if ($Player->State == PlayerState::Join && $Player->Hand->canSplit()) {
            $this->addMessage(emoji(0x1F449) . " You can `hit`, `stand`, `split`, `double down` or `surrender`.");
            $this->keyboard = [
                [
                    [
                        'text' => "Hit",
                        'callback_data' => '/hit'
                    ],
                    [
                        'text' => "Stand",
                        'callback_data' => "/stand"
                    ]
                ],
                [
                    [
                        'text' => "Split",
                        'callback_data' => '/split'
                    ],
                    [
                        'text' => "Double down",
                        'callback_data' => "/doubledown"
                    ],
                    [
                        'text' => "Surrender",
                        'callback_data' => "/surrender"
                    ]
                ]
            ];
        } elseif ($Player->State == PlayerState::Join) {
            $this->addMessage(emoji(0x1F449) . " You can `hit`, `stand`, `double down` or `surrender`.");
            $this->keyboard = [
                [
                    [
                        'text' => "Hit",
                        'callback_data' => '/hit'
                    ],
                    [
                        'text' => "Stand",
                        'callback_data' => "/stand"
                    ]
                ],
                [
                    [
                        'text' => "Double down",
                        'callback_data' => "/doubledown"
                    ],
                    [
                        'text' => "Surrender",
                        'callback_data' => "/surrender"
                    ]
                ]
            ];
        } else {
            $this->addMessage(emoji(0x1F449) . " You can `hit`, `stand` or `double down`.");
            $this->keyboard = [
                [
                    [
                        'text' => "Hit",
                        'callback_data' => '/hit'
                    ],
                    [
                        'text' => "Stand",
                        'callback_data' => "/stand"
                    ],
                    [
                        'text' => "Double down",
                        'callback_data' => "/doubledown"
                    ]
                ]
            ];
        }
    }

    private function player_state(Player $Player)
    {
        if ($Player->State == PlayerState::Stand) {
            $this->addMessage("💬 *" . $Player->user->getName() . "* stands.");
        } elseif ($Player->State == PlayerState::Bust) {
            $this->addMessage("☠ *" . $Player->user->getName() . "* is bust.");
        } elseif ($Player->State == PlayerState::TwentyOne) {
            $this->addMessage("✌ *" . $Player->user->getName() . "* has twenty one! * They stand.");
        }
    }

    public function hit(Player $Player)
    {
        $this->addMessage("👌 *" . $Player->user->getName() . "* hits.");
        $this->addMessage("🃏 *" . $Player->user->getName() . "'s* cards: " . $Player->Hand->getHandString() . " (" . $Player->Hand->Value . ")");
        $this->player_state($Player);
    }

    public function split(\GroupBot\Brains\CardGame\Types\Player $Player1, \GroupBot\Brains\CardGame\Types\Player $Player2)
    {
        $this->addMessage("👌 *" . $Player1->user->getName() . "* has split their hand into two and matched their bet of " . ($Player1->bet + 0) . ". The dealer has dealt them one new card per hand.");
        $this->addMessage("🃏 Hand 1: " . $Player1->Hand->getHandString() . " (" . $Player1->Hand->Value . ")");
        $this->addMessage("🃏 Hand 2: " . $Player2->Hand->getHandString() . " (" . $Player2->Hand->Value . ")");
    }

    public function split_wrong_turn()
    {
        $this->addMessage("👎 You can only split on your first turn.");
    }

    public function split_wrong_cards()
    {
        $this->addMessage("👎 You can only split with two equal ranked cards on your first turn.");
    }

    public function split_dealer_not_enough_money()
    {
        $this->addMessage("👎 " . COIN_TAXATION_BODY . " doesn't have enough Coin to accept a split, sorry.");
    }

    public function split_not_enough_money(Player $Player)
    {
        $this->addMessage("👎 *" . $Player->user->getName() . "*, you don't have enough money to split");
    }

    public function split_only_once()
    {
        $this->addMessage("👎 You can only split once!");
    }

    public function surrender(Player $player)
    {
        $out = "🏳 *" . $player->user->getName() . "* surrenders! The dealer returns half their bet. ";
        $out .= " (`" . $player->user->getBalance() . "`)";
        $this->addMessage($out);
    }

    public function surrender_wrong_turn()
    {
        $this->addMessage("👎 You can only surrender on your first turn!");
    }

    public function surrender_free(Player $Player)
    {
        $this->addMessage("🏳 *" . $Player->user->getName() . "* surrenders! However, as they're on a free bet, they receive no Coin.");
    }

    public function double_down(Player $Player)
    {
        $this->addMessage("👌 *" . $Player->user->getName() . "* doubles down, doubling their bet to " . ($Player->bet + 0) . ".");
        $this->addMessage("💬 *" . $Player->user->getName() . "* is dealt another card.");
        $this->addMessage("🃏 *" . $Player->user->getName() . "'s* cards: " . $Player->Hand->getHandString() . " (" . $Player->Hand->Value . ")");
        $this->player_state($Player);
    }

    public function double_down_not_enough_money(Player $Player)
    {
        $this->addMessage("👎 *" . $Player->user->getName() . "*, you don't have enough money to double down.");
    }

    public function double_down_dealer_not_enough_money()
    {
        $this->addMessage("👎 " . COIN_TAXATION_BODY . " doesn't have enough Coin to accept a double down, sorry.");
    }

    /**
     * @param Game $Game
     */
    public function next_turn(\GroupBot\Brains\CardGame\Types\Game $Game)
    {
        /** @var Player $Player */
        $Player = $Game->getCurrentPlayer();

        if ($Player->State == PlayerState::Join && $Player->player_no != 0) {
            $this->addMessage("🃏 *" . $Player->user->getName() . "'s* hand: " . $Player->Hand->getHandString() . " (" . $Player->Hand->Value . ")");
        }
        if (!($Player->State == PlayerState::Join && $Player->player_no == 0)) {
            $this->addMessage("🃏 Dealer's hand: " . $Game->Dealer->Hand->getHandString() . " (" . $Game->Dealer->Hand->Value . ")");
        }
        if ($Game->getNumberOfPlayers() > 1) {
            $out = "💬 It is now *" . $Player->user->getPrefixedUserName() . "'s* turn";
            if ($Player->split == 1) {
                $out .= " (hand one)";
            } elseif ($Player->split == 2) {
                $out .= " (hand two)";
            }
            $this->addMessage($out . ".");
        } else {
            $this->addMessage("👉 Please place your move.");
        }
        $this->next_turn_options($Game);
    }

    public function dealer_done(Game $Game, Player $Dealer)
    {
        if ($Game->getNumberOfPlayers() > 1) {
            $this->addMessage("💬 All players have stood or are bust. The dealer draws cards:");
            $this->addMessage("🃏 " . $Dealer->Hand->getHandString() . " (" . $Dealer->Hand->Value . ")");
        } else {
            $this->addMessage('🃏 The dealer draws cards ' . $Dealer->Hand->getHandString() . " (" . $Dealer->Hand->Value . ")");
        }

        if ($Dealer->State == PlayerState::Bust) {
            $this->addMessage('☠ The dealer is bust.');
        } elseif ($Dealer->State == PlayerState::Stand) {
            $this->addMessage('💡 The dealer stands.');
        }

        if ($this->chat->isPrivate()) {
            $this->keyboard =
                [
                    [
                        [
                            'text' => "💰 Again - bet 1",
                            'callback_data' => '/bjstart'
                        ],
                        [
                            'text' => "💰 Again - bet all",
                            'callback_data' => '/bjstart all'
                        ],
                    ],
                    [
                        [
                            'text' => emoji(0x1F3AE) . ' Back to games menu',
                            'callback_data' => '/help games'
                        ],
                        [
                            'text' => emoji(0x1F6AA) . ' Main menu',
                            'callback_data' => '/help'
                        ]
                    ]
                ];
        } else {
            $this->keyboard =
                [
                    [
                        [
                            'text' => "💰 Again - bet 1",
                            'callback_data' => '/blackjack'
                        ],
                        [
                            'text' => "💰 Again - bet all",
                            'callback_data' => '/blackjack all'
                        ],
                    ],
                ];
        }
    }
}
