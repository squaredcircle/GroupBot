<?php

$xml = file_get_contents("ftp://ftp2.bom.gov.au/anon/gen/fwo/IDW12300.xml");
$bom = new \SimpleXMLElement($xml);

//foreach ($bom as $location) {
//    if (strcmp($location->name, 'per') ===0) {
//        $descr = $this->uv_description($location->index);
//        return new UV($location->index, $descr[0], $descr[1]);
//    }
//}

$forecast = [];

foreach ($bom->forecast[0] as $area) {
    if ($area['aac'] == 'WA_ME001') {
        foreach ($area as $forecast_period) {
            $forecast[] = [
                'start' => $forecast_period['start-time-local'],
                'end' => $forecast_period['end-time-local'],
                'forecast' => $forecast_period->text
            ];
        }
    } elseif ($area['aac'] == 'WA_PT053') {
        foreach ($area as $forecast_period) {
            foreach ($forecast_period->element as $x)
                echo $x['type'];
        }
    }
}
