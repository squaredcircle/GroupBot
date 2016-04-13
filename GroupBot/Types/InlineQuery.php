<?php

/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 6/11/2015
 * Time: 10:59 PM
 */

namespace GroupBot\Types;


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
        $tmp = mb_strtolower($this->query, 'UTF-8');
        $tmp = str_ireplace('v', 'w', $tmp);
        $tmp = preg_replace('/(\w)\1+/', '$1', $tmp);
        $tmp = str_ireplace('sh', 'sz', $tmp);
        $tmp = str_ireplace('sch', 'sz', $tmp);
        $tmp = str_ireplace('ch', 'cz', $tmp);
        $tmp = str_ireplace('ö', 'ó', $tmp);
        $tmp = str_ireplace('ü', 'ó', $tmp);
        $tmp = str_ireplace('zei', 'caj', $tmp);
        $tmp = str_ireplace('ei', 'aj', $tmp);
        $tmp = str_ireplace('sie', 'się', $tmp);

        $out = "kurwa, " . $tmp;

        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 1),
            'title' => 'german -> kurwa translation',
            'input_message_content' => [
                'message_text' => $out,
                'parse_mode' => 'markdown'
            ]
        );

        $tmp = mb_strtolower($this->query, 'UTF-8');
        $tmp = str_ireplace('v', 'w', $tmp);
        $tmp = str_ireplace('oo', 'ó', $tmp);
        $tmp = preg_replace('/(\w)\1+/', '$1', $tmp);
        $tmp = str_ireplace('sh', 'sz', $tmp);
        $tmp = str_ireplace('sch', 'sz', $tmp);
        $tmp = str_ireplace('ch', 'cz', $tmp);
        $tmp = str_ireplace('ei', 'aj', $tmp);
        $tmp = str_ireplace('w', 'ł', $tmp);
        $tmp = str_ireplace('y', 'j', $tmp);
        $tmp = str_ireplace('ts', 'c', $tmp);
        $tmp = str_ireplace('ow', 'ą', $tmp);
        $tmp = str_ireplace('ds', 'dz', $tmp);
        $tmp = str_ireplace('isi', 'iżi', $tmp);

        $out = "kurwa, " . $tmp;

        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 2),
            'title' => 'english -> kurwa translation',
            'input_message_content' => [
                'message_text' => $out,
                'parse_mode' => 'markdown'
            ]
        );

        $out = "";
        $tmp = array_reverse(explode(" ", $this->query));
        foreach ($tmp as $word) {
            $out .= " " . $word;
        }
        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 3),
            'title' => 'talk backwards',
            'input_message_content' => [
                'message_text' => $out,
                'parse_mode' => 'markdown'
            ]
        );

        $out = "*" . $this->from->getName() . "* " . $this->query;
        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 4),
            'title' => 'me',
            'input_message_content' => [
                'message_text' => $out,
                'parse_mode' => 'markdown'
            ]
        );
    }
}