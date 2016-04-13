<?php
$arg = 'T';
$vehicle = (    ($arg == 'B') ? 'bus' :
                ($arg == 'A') ? 'airplane' :
                ($arg == 'T') ? 'train' :
                ($arg == 'C') ? 'car' :
                ($arg == 'H') ? 'horse' :
                    'feet');
echo $vehicle;