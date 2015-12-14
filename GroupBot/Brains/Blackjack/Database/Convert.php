<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 13/12/2015
 * Time: 11:00 AM
 */

namespace GroupBot\Brains\Blackjack\Database;


use GroupBot\Brains\Blackjack\Enums\PlayerState;
use GroupBot\Brains\Blackjack\Types\Card;
use GroupBot\Brains\Blackjack\Types\Hand;

class Convert
{
    public function __construct()
    {
    }

    public function cardFromString($card)
    {
        switch ($card % 13) {
            case 0:
                $rank = 'A';
                break;
            case 10:
                $rank = 'J';
                break;
            case 11:
                $rank = 'Q';
                break;
            case 12:
                $rank = 'K';
                break;
            default:
                $rank = $card % 13 + 1;
        }

        switch (floor($card / 13)) {
            case 0:
                $suite = '♠';
                break;
            case 1:
                $suite = '♥';
                break;
            case 2:
                $suite = '♦';
                break;
            case 3:
                $suite = '♣';
                break;
            default:
                $suite = '';
        }

        return new Card($rank, $suite);
    }

    public function cardToString(Card $Card)
    {
        switch ($Card->rank) {
            case 'A':
                $rank = 0;
                break;
            case 'J':
                $rank = 10;
                break;
            case 'Q':
                $rank = 11;
                break;
            case 'K':
                $rank = 12;
                break;
            default:
                $rank = $Card->rank - 1;
        }

        switch ($Card->suite) {
            case '♠':
                $suite = 0;
                break;
            case '♥':
                $suite = 1;
                break;
            case '♦':
                $suite = 2;
                break;
            case '♣':
                $suite = 3;
                break;
            default:
                return false;
        }

        return $suite * 13 + $rank;
    }

    public function handFromString($cards)
    {
        $card_array = explode(",", $cards);
        $Cards = array();

        foreach($card_array as $i) {
            $Cards[] = $this->cardFromString($i);
        }

        return $Cards;
    }

    public function handToString(Hand $Hand)
    {
        $out = '';
        foreach ($Hand->Cards as $Card) {
            $out .= ',' . $this->cardToString($Card);
        }

        return substr($out, 1);
    }

    public function stateFromString($state)
    {
        switch ($state) {
            case "join":
                return new PlayerState(PlayerState::Join);
                break;
            case "hit":
                return new PlayerState(PlayerState::Hit);
                break;
            case "stand":
                return new PlayerState(PlayerState::Stand);
                break;
            case "bust":
                return new PlayerState(PlayerState::Bust);
                break;
            case "bj":
                return new PlayerState(PlayerState::BlackJack);
                break;
            case "21":
                return new PlayerState(PlayerState::TwentyOne);
                break;
            case "deal":
                return new PlayerState(PlayerState::Dealer);
                break;
        }
        return false;
    }

    public function stateToString(PlayerState $State)
    {
        switch ($State) {
            case PlayerState::Join:
                return "join";
                break;
            case PlayerState::Hit:
                return "hit";
                break;
            case PlayerState::Stand:
                return "stand";
                break;
            case PlayerState::Bust:
                return "bust";
                break;
            case PlayerState::BlackJack:
                return "bj";
                break;
            case PlayerState::TwentyOne:
                return "21";
                break;
            case PlayerState::Dealer:
                return "deal";
                break;
        }
        return false;
    }

}