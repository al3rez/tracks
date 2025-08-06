<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

date_default_timezone_set('UTC');

error_reporting(E_ALL);
ini_set('display_errors', $_ENV['TRACKS_ENV'] === 'development' ? '1' : '0');