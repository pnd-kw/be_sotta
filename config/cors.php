<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://sottasouvenir.com', 'http://localhost:3000', 'https://sotta-a7lp7uav4-krisna-pandu-wibowos-projects.vercel.app', 'https://sotta.vercel.app'],

    'allowed_origins_pattern' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
