#!/usr/bin/env php
<?php
date_default_timezone_set('America/Chicago');

// Include composer autoload file
require __DIR__.'/vendor/autoload.php';

use Carbon\Carbon;

try
{
    // Set up the environment variables
    $dotenv = new Dotenv\Dotenv(__DIR__);
    $dotenv->load();
    $dotenv->required([
        'BLIZZARD_KEY',
        'BLIZZARD_SECRET'
    ]);
}
catch (\Dotenv\Exception\ValidationException $e)
{
    exit($e->getMessage());
}

$realms = $argv;
array_shift($realms);

// Create a new Blizzard client with Blizzard API key and secret
$client = new \BlizzardApi\BlizzardClient(getenv('BLIZZARD_KEY'), getenv('BLIZZARD_SECRET'));

// Create a new API service with configured Blizzard client
$wow = new \BlizzardApi\Service\WorldOfWarcraft($client);

// Set up carbon
$carbon = new Carbon();

// Loop through the list of realms
foreach ($realms as $realm) {
    // use the wow api to get the realm status for the current realm
    $response = $wow->getRealmStatus(['realms' => $realm]);
    $realminfo=json_decode($response->getBody(), true);
    $realminfo = $realminfo['realms'][0];

    // file to write
    $filename = dirname(__FILE__) . "/output/" . $realminfo['name'] . ".txt";
    $file=fopen($filename, "a");
    // example output
    // [2017-06-20 10:40:01] Proudmoore is currently up.
    $output = "[" . $carbon::now() . "] " . $realminfo['name'] . " is currently " . ($realminfo['status']=="true" ? "up." : "down.") . PHP_EOL;
    fwrite($file,$output);
    fclose($file);
}

?>
