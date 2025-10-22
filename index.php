<?php
session_start();

error_reporting(E_ERROR | E_PARSE);

include("helper/ConfigFactory.php");

$configFactory = new ConfigFactory();
$router = $configFactory->get("router");

$router->executeController($_GET["controller"], $_GET["method"]);
