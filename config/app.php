<?php
return [
    'name'     => $_ENV['APP_NAME']     ?? 'KewanFarma',
    'env'      => $_ENV['APP_ENV']      ?? 'production',
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'      => $_ENV['APP_URL']      ?? '',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Africa/Maputo',
];
