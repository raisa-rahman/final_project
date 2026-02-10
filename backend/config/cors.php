<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register', 'logout'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:5173'], 

'supports_credentials' => true,

    'allowed_origins_patterns' => [],

    'exposed_headers' => [],

    'max_age' => 0,

];