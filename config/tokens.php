<?php

return [
    'access_lifetime' => env('ACCESS_TOKEN_LIFETIME', 3600*10), // minutes
    'refresh_lifetime' => env('REFRESH_TOKEN_LIFETIME', 30), // days
];
