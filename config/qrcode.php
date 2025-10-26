<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QR Code Generator Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for configuring the simple-qrcode package.
    | We're using SVG format which doesn't require ImageMagick.
    |
    */

    'default' => [
        'format' => 'svg', // Use SVG instead of PNG to avoid ImageMagick requirement
        'size' => 300,
        'margin' => 2,
        'errorCorrection' => 'H',
    ],
];

