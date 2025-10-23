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

class ConfigFactory
{
    private $config;
    private $objetos;
    private Logger $logger;

    private $conexion;
    private $renderer;

    public function __construct()
    {
        $this->config = parse_ini_file("config/config.ini");
        $this->logger = new Logger();

        $this->conexion = new MyConexion(
            $this->config["server"],
            $this->config["user"],
            $this->config["password"],
            $this->config["database"],
            $this->logger
        );

        $this->renderer = new MustacheRenderer("vista");

        $this->objetos["router"] = new NewRouter($this, "PokemonController", "base");

        // $this->objetos["LoginController"] = new LoginController(new LoginModel($this->conexion), $this->renderer);
        // $this->objetos["PokemonController"] = new PokemonController(new PokemonModel($this->conexion), $this->renderer);

        $this->objetos["RegisterController"] = new RegisterController(
            new GeneroDao($this->conexion, $this->logger),
            new UsuarioDao($this->conexion),
            $this->renderer
        );
    }

    public function get($objectName)
    {
        return $this->objetos[$objectName];
    }
}
