<?php

$fill = '#000000';
if(isset($_REQUEST['fill']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $_REQUEST['fill'])){
    $fill = $_REQUEST['fill'];
}

$stroke = '#000000';
if(isset($_REQUEST['stroke']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $_REQUEST['stroke'])){
    $stroke = $_REQUEST['stroke'];
}

header('Content-Type: image/svg+xml');

echo <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"  width="24" height="24" viewBox="0 0 24 24">
    <path fill="{$fill}" stroke="{$stroke}" d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" />
</svg>
SVG;
