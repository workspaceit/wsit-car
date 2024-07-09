<?php

return [
    'secret' => env('INVISIBLE_RECAPTCHA_SECRETKEY'),
    'sitekey' => env('INVISIBLE_RECAPTCHA_SITEKEY'),
    'options' => [
        'timeout' => 30,
    ],
];
