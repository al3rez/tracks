<?php

require_once dirname(__DIR__) . '/config/boot.php';

use Tracks\Application;

$app = Application::getInstance();
$app->run();