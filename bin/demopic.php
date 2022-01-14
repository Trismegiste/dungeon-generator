<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$generator = new Trismegiste\MapGen\PackedRoom();

$map = $generator->generate(20, 20, 1, 0.8, true);
$width = count($map);
$height = count($map[0]);
$target = imagecreatetruecolor($width, $height);

$color = [
    '.' => imagecolorallocate($target, 255, 255, 255),
    '~' => imagecolorallocate($target, 0, 0, 0),
    '#' => imagecolorallocate($target, 255, 0, 0),
];

foreach ($map as $x => $col) {
    foreach ($col as $y => $cell) {
        imagesetpixel($target, $x, $y, $color[$cell]);
    }
}

imagepng($target, 'dump.png');

