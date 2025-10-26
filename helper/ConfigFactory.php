<?php
include_once("helper/MyConexion.php");
include_once("helper/IncludeFileRenderer.php");
include_once("helper/NewRouter.php");
include_once('vendor/mustache/src/Mustache/Autoloader.php');
include_once("helper/MustacheRenderer.php");
include_once('helper/Logger.php');
include_once("controller/RegisterController.php");
include_once("model/dao/GeneroDao.php");
include_once("model/dao/UsuarioDao.php");
include_once("controller/LoginController.php");
include_once("controller/ValidatorController.php");
include_once("model/dao/ValidatorModelDao.php");
include_once("model/dao/RegisterModelDao.php");
include_once("model/dao/LoginModelDao.php");

class ConfigFactory
{
    private $config;
    private $objetos;
    private Logger $logger;

    private $conexion;
    private $renderer;

    public function __construct()
    {
        $this->config = parse_ini_file("config/config.ini", true);
        $this->logger = new Logger();

        $this->conexion = new MyConexion(
            $this->config['database']["server"],
            $this->config['database']["user"],
            $this->config['database']["password"],
            $this->config['database']["database"],
            $this->logger
        );

        $this->renderer = new MustacheRenderer("vista");

        $this->objetos["router"] = new NewRouter($this, "PokemonController", "base");

        $this->objetos["RegisterController"] = new RegisterController(
            new GeneroDao($this->conexion, $this->logger),
            new UsuarioDao($this->conexion),
            $this->renderer,
            new RegisterModelDao($this->conexion, new UsuarioDao($this->conexion))
        );
        $this->objetos['LoginController'] = new LoginController(
            $this->renderer,
            new LoginModelDao(new UsuarioDao($this->conexion))
        );

        $this->objetos['ValidatorController'] = new ValidatorController(
            new ValidatorModelDao(new UsuarioDao($this->conexion))
        );
    }

    public function get($objectName)
    {
        return $this->objetos[$objectName];
    }
}
