<?php

require(__DIR__ . "/../app/Bootstrap.php");

use Woxapp\Scaffold\Application;

$app = new Application($di);
$app->dispatch()->send();
