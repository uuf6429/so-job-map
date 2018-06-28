<?php

if (!function_exists('getEnvVar')) {
    function getEnvVar(string $name, $default)
    {
        return \in_array($value = getenv($name), [false, null], true) ? $default : $value;
    }
}

return [
    'redis_dsn' => getEnvVar('redis_dsn', 'tcp://redis:6379'),
    'cache_ttl' => (int)getEnvVar('cache_ttl', 31536000),
    'gmaps_backend_api_key' => getEnvVar('gmaps_backend_api_key', ''),
    'gmaps_frontend_api_key' => getEnvVar('gmaps_frontend_api_key', ''),
];
