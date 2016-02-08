<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 3:05 AM
 */
namespace GroupBot\Command;

use GroupBot\Base\Telegram;
use GroupBot\Types\Command;

class t_wiki extends Command
{
    public function t_wiki()
    {
        if (strlen($this->Message->text) == 0) {
            Telegram::talk($this->Message->Chat->id, ">not understanding wiki");
            return 0;
        }

        $url = 'http://en.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&exsentences=3&exintro=&explaintext=&exsectionformat=plain&titles=';
        $url .= urlencode($this->Message->text) . "&redirects=";

        $data = json_decode(file_get_contents($url));

        $info = key($data->{'query'}->{'pages'});

        if ($info == '-1') {
            Telegram::talk($this->Message->Chat->id, "doesn't exist, fam");
        } else {
            $out =  current($data->{'query'}->{'pages'})->{'extract'};
            Telegram::talk($this->Message->Chat->id, $out);
        }
        return true;
    }
}