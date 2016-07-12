<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 7/11/2015
 * Time: 11:17 PM
 */
namespace GroupBot\Command;


use GroupBot\Libraries\Converter;
use GroupBot\Telegram;
use GroupBot\Types\Command;

class convert extends Command
{
    /** @var  Converter */
    private $Converter;

    /** @var  string */
    private $out;

    private function help()
    {
        $this->out .= "✍ Like this fam: "
            . "\n"
            . "\n   • `/convert` *2ft m*"
            . "\n   • `/convert` *90 deg rad*"
            . "\n   • `/convert` *5 lb*"
            . "\n"
            . "\nThe default conversion is to SI."
            . "\nYou can see all the compatible units [here](https://github.com/olifolkerd/convertor)";
        Telegram::talk($this->Message->Chat->id, $this->out);
        return true;
    }

    private function getSI($to_units)
    {
        $SI_units = ['m', 'm2', 'l', 'kg', 'mps', 'rad', 'c', 'pa', 's', 'j'];

        foreach ($SI_units as $unit) {
            if (in_array($unit, $to_units)) return $unit;
        }
        return false;
    }

    public function main()
    {
        $this->out = '';

        if ($this->noParams() == 3)
        {
            $number = $this->getParam();
            $from = $this->getParam(1);
            $to = $this->getParam(2);
        }
        elseif ($this->noParams() == 2)
        {
            $arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$this->getParam());
            $number = $arr[0];
            $from = $arr[1];
            $to = $this->getParam(1);
        }
        elseif ($this->isParam())
        {
            $arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$this->getParam());
            $number = $arr[0];
            $from = $arr[1];
        }
        else
        {
            return $this->help();
        }

        if (is_numeric($number)) {
            $this->Converter = new Converter();
            try {
                $to_units = $this->Converter->getUnits($from);
            } catch (\Exception $e) {
                $this->out = "Can't find that unit m8.\n\n";
                return $this->help();
            }
            if (!isset($to)) {
                $to = $this->getSI($to_units);
            } else if (!in_array($to, $to_units)) {
                $this->out = "Can't find that unit m8.\n\n";
                return $this->help();
            }
        } else {
            return $this->help();
        }

        $this->Converter = new Converter($number, $from);
        $result = $this->Converter->to($to);

        $this->out = "`$result` *$to*";

        Telegram::talk($this->Message->Chat->id, $this->out);
        return true;
    }
}