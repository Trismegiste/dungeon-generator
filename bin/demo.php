<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$generator = new Trismegiste\MapGen\PackedRoom();

$map = $generator->generate(20, 20, 0, 0.5, true);

foreach ($map as $col) {
    foreach ($col as $cell) {
        echo $cell;
    }
    echo PHP_EOL;
}

echo PHP_EOL;
