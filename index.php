<?php
session_start();

error_reporting(E_ERROR | E_PARSE);

$config = parse_ini_file("config/config.ini", true);

if (isset($config['app']['timezone'])) {
    date_default_timezone_set($config['app']['timezone']);
}

include("helper/ConfigFactory.php");

$configFactory = new ConfigFactory();
$router = $configFactory->get("router");

$router->executeController($_GET["controller"], $_GET["method"]);
