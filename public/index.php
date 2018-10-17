<?php

error_reporting(E_ALL);
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
ini_set('error_reporting', E_ALL);

require(__DIR__ . "/../app/Bootstrap.php");
require(__DIR__ . '/../app/Routes.php');

use Woxapp\Scaffold\Application;

$app = new Application($di);
$app->dispatch()->send();