<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = new \uuf6429\SOJobMap\Service\Config();
$client = new \Predis\Client($config['redis_dsn']);
$client->connect();
$pool = new \Cache\Adapter\Predis\PredisCachePool($client);
$cachePlugin = new \Http\Client\Common\Plugin\CachePlugin(
    $pool,
    \Http\Discovery\StreamFactoryDiscovery::find(),
    [
        'respect_cache_headers' => false,
        'default_ttl' => null,
        'cache_lifetime' => $config['cache_ttl'],
    ]
);
$adapter = new Http\Adapter\Guzzle6\Client();
$pluginClient = new \Http\Client\Common\PluginClient($adapter, [$cachePlugin]);
$geocoder = new \Geocoder\Provider\GoogleMaps\GoogleMaps($pluginClient, 'en', $config['gmaps_backend_api_key']);

$feedReader = new \uuf6429\SOJobMap\Service\FeedReader($geocoder, $pool);
$mainView = new \uuf6429\SOJobMap\Service\MainView($config['gmaps_frontend_api_key']);
$app = new \uuf6429\SOJobMap\Application($config, $feedReader, $mainView);
$app->run();
