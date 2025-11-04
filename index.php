<?php
require_once __DIR__ . '/vendor/autoload.php';

StartSession::start();

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$config = parse_ini_file(__DIR__ . "/config/config.ini", true);

if (isset($config['app']['timezone'])) {
    date_default_timezone_set($config['app']['timezone']);
}

$configFactory = new ConfigFactory();
$router = $configFactory->get("router");

$router->executeController($_GET["controller"], $_GET["method"]);
