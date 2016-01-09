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
        $this->from = new User($query['from']);
        $this->query = $query['query'];
        $this->offset = $query['offset'];
        $this->parseQuery();
    }

    private function parseQuery()
    {
        $out = "";
        $tmp = array_reverse(explode(" ", $this->query));
        foreach($tmp as $word) {
            $out .= " " . $word;
        }
        $this->results[] = array(
            'type' => 'article',
            'id' => strval(intval($this->id) + 1),
            'title' => 'talk backwards',
            'message_text' => $out
        );
    }
}