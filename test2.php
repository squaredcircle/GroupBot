<?php

include('GroupBot/Libraries/Carbon.php');

$xml = file_get_contents("ftp://ftp2.bom.gov.au/anon/gen/fwo/IDW12300.xml");
$bom = new \SimpleXMLElement($xml);

$forecast = [];

foreach ($bom->forecast[0] as $area)
{
    $index = 0;
    foreach ($area as $forecast_period)
    {
        if (++$index > 5) break;

        $start_time = (string)$forecast_period['start-time-local'];
        $dayOfWeek = \GroupBot\Libraries\Carbon::parse($start_time)->format('l');

        if ($area['aac'] == 'WA_ME001' || $area['aac'] == 'WA_PT053')
        {
            foreach ($forecast_period->element as $value) {
                $type = (string)$value['type'];
                $forecast[$dayOfWeek][$type] = (string)$value;
            }

            foreach ($forecast_period->text as $value) {
                $type = (string)$value['type'];
                $forecast[$dayOfWeek][$type] = (string)$value;
            }
        }
    }
}

print_r($forecast);
