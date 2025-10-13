<?php

return [
    'format' => 'png',
    'size' => 200,
    'margin' => 2,
    'errorCorrection' => 'H',
    'encoding' => 'UTF-8',
    'color' => [
        'foreground' => [0, 0, 0],
        'background' => [255, 255, 255],
    ],
    'style' => 'square',
    'eye' => 'square',
    'image_backend' => 'gd', // или 'imagick'
];