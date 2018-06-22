<?php

namespace uuf6429\SOJobMap\Service;

class MainView
{
    protected $gmapsApiKey;
    protected $pageTitleText = 'StackOverflow Job Map';
    protected $searchPlaceholderText = 'Search jobs...';

    public function __construct(string $gmapsApiKey)
    {
        $this->gmapsApiKey = $gmapsApiKey;
    }

    public function render(): void
    {
        echo <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <title>{$this->pageTitleText}</title>
        <meta name="viewport" content="initial-scale=1.0">
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="main.css?v={$this->getFileVersion('main.css')}">
    </head>
    <body>
        <div id="map"></div>
        <div id="search-control"><input type="text" id="search-input" title="" placeholder="{$this->searchPlaceholderText}"/></div>
        <div id="progress-control"><div id="progress-bar"></div></div>
        <script src="https://maps.googleapis.com/maps/api/js?key={$this->gmapsApiKey}&callback=initMap" async defer></script>
        <script src="https://cdn.jsdelivr.net/npm/jsonpipe@2/jsonpipe.min.js"></script>
        <script src="main.js?v={$this->getFileVersion('main.js')}"></script>
    </body>
</html>
HTML;
    }

    private function getFileVersion(string $file): string
    {
        return filemtime(__DIR__ . '/../../public/' . $file);
    }
}
