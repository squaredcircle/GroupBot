<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 10:59 PM
 */

namespace GroupBot\Types;


use GroupBot\Brains\Translate;
use GroupBot\Brains\Zalgo;

class InlineQuery
{
    public $id, $from, $query, $offset;
    public $results = array();

    public function __construct($query)
    {
        $this->id = $query['id'];
        $this->query = $query['query'];
        $this->offset = $query['offset'];
        $this->from = new User();
        $this->from->first_name = $query['from']['first_name'];
        $this->from->last_name = isset($query['from']['last_name']) ? $query['from']['last_name'] : NULL;
        $this->from->username = isset($query['from']['username']) ? $query['from']['username'] : NULL;
        $this->from->id = $query['from']['id'];
        $this->parseQuery();
    }

    private function parseQuery()
    {
        $out = "";
        $tmp = array_reverse(explode(" ", $this->query));
        foreach ($tmp as $word) {
            $out .= " " . $word;
        }
        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 1),
            'title' => 'backwards',
            'input_message_content' => [
                'message_text' => $out,
                'parse_mode' => 'markdown'
            ]
        );

        $out = "*" . $this->from->getName() . "* " . $this->query;
        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 2),
            'title' => 'me',
            'input_message_content' => [
                'message_text' => $out,
                'parse_mode' => 'markdown'
            ]
        );

        $out = Zalgo::speak($this->query);
        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 3),
            'title' => 'zalgo',
            'input_message_content' => [
                'message_text' => $out,
                'parse_mode' => 'markdown'
            ]
        );

        $out = "*" . $this->query . "*";
        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 4),
            'title' => 'bold',
            'input_message_content' => [
                'message_text' => $out,
                'parse_mode' => 'markdown'
            ]
        );

        $out = "_" . $this->query . "_";
        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 5),
            'title' => 'italic',
            'input_message_content' => [
                'message_text' => $out,
                'parse_mode' => 'markdown'
            ]
        );

        $word = strlen($this->query) > 10 ? substr($this->query, 10) : $this->query;
        $word = strtoupper($word);
        $out = implode(' ', str_split($word));
        foreach (str_split(substr($word,1)) as $char) {
            $out .= "\n$char";
        }
        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 6),
            'title' => 'L O N D O N',
            'input_message_content' => [
                'message_text' => "*$out*",
                'parse_mode' => 'markdown'
            ]
        );

        $Translate = new Translate();
        $lang = $Translate->detectLanguage($this->query);
        if ($lang != 'English') {
            $translation = $Translate->translate($this->query, 'English');
            $out = "_(" . $translation['lang_source'] . ")_* " . $this->query . "*"
                . "\n"
                . "_(English)_* " . $translation['result'][0] . "*";
        } else {
            $out = $this->query;
        }
        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 7),
            'title' => 'translate to english',
            'input_message_content' => [
                'message_text' => $out,
                'parse_mode' => 'markdown'
            ]
        );
        
        $file = \GroupBot\Brains\Speak::createAudioFile($this->query);
        $this->results[] = array(
            'type' => 'voice',
            'id' => strval(intval($this->id) + 8),
            'title' => 'vocalise with hts',
            'voice_url' => "https://www.drinkwatchprogram.com/bot/speech/$file"
        );

        $file = \GroupBot\Brains\Speak::createAudioFile($this->query, 'espeak');
        $this->results[] = array(
            'type' => 'voice',
            'id' => strval(intval($this->id) + 9),
            'title' => 'vocalise with espeak',
            'voice_url' => "https://www.drinkwatchprogram.com/bot/speech/$file"
        );
    }
}