<?php

return [
    'uri'      => env('NEO4J_URI', 'neo4j+s://localhost'),
    'username' => env('NEO4J_USERNAME', 'neo4j'),
    'password' => env('NEO4J_PASSWORD', ''),
    'database' => env('NEO4J_DATABASE', 'neo4j'),
];
