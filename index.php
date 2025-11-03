<?php


require_once __DIR__ . '/vendor/autoload.php';

include_once "helper/StartSession.php";

error_reporting(E_ERROR | E_PARSE);

StartSession::start();

$config = parse_ini_file("config/config.ini", true);

if (isset($config['app']['timezone'])) {
    date_default_timezone_set($config['app']['timezone']);
}

include "helper/ConfigFactory.php";

$configFactory = new ConfigFactory();
$router = $configFactory->get("router");

$router->executeController($_GET["controller"], $_GET["method"]);
