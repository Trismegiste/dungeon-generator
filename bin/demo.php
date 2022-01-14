<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$generator = new Trismegiste\MapGen\PackedRoom();

$map = $generator->generate(30, 10, 1);

foreach ($map as $col) {
    foreach ($col as $cell) {
        echo $cell;
    }
    echo PHP_EOL;
}

echo PHP_EOL;
