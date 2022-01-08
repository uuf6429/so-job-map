<?php

use Cache\Adapter\Predis\PredisCachePool;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Http\Client\Common\Plugin\CachePlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\StreamFactoryDiscovery;
use Predis\Client;
use uuf6429\SOJobMap\Application;
use uuf6429\SOJobMap\Service\Config;
use uuf6429\SOJobMap\Service\FeedReader;
use uuf6429\SOJobMap\Service\MainView;

require_once __DIR__ . '/../vendor/autoload.php';

$config = new Config();
$client = new Client($config['redis_dsn']);
$client->connect();
$pool = new PredisCachePool($client);
$cachePlugin = new CachePlugin(
    $pool,
    StreamFactoryDiscovery::find(),
    [
        'respect_cache_headers' => false,
        'default_ttl' => null,
        'cache_lifetime' => $config['cache_ttl'],
    ]
);
$adapter = new Http\Adapter\Guzzle6\Client();
$pluginClient = new PluginClient($adapter, [$cachePlugin]);
$geocoder = new GoogleMaps($pluginClient, 'en', $config['gmaps_backend_api_key']);

$feedReader = new FeedReader($geocoder, $pool);
$mainView = new MainView($config['gmaps_frontend_api_key']);
$app = new Application($config, $feedReader, $mainView);
$app->run();
