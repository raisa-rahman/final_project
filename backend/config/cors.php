<?php

return [
    // Paths that need to share cookies (sanctum, login, and admin api)
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register', 'user'],

    'allowed_methods' => ['*'],

    // This MUST exactly match your Vue.js URL
    'allowed_origins' => ['http://localhost:5173'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];