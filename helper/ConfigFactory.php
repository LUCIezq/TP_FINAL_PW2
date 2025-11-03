<?php

include_once 'vendor/mustache/src/Mustache/Autoloader.php';


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
            new LoginModelDao(new UsuarioDao($this->conexion)),
            new UsuarioDao($this->conexion)
        );

        $this->objetos['ValidatorController'] = new ValidatorController(
            new ValidatorModelDao(new UsuarioDao($this->conexion))
        );

        $this->objetos['HomeController'] = new HomeController($this->renderer, new UsuarioDao($this->conexion), new SolicitudPartidaDao($this->conexion, new UsuarioDao($this->conexion)));

        $this->objetos['SolicitudPartidaController'] = new SolicitudPartidaController(new SolicitudPartidaDao($this->conexion, new UsuarioDao($this->conexion)));

        $this->objetos['UsuarioController'] = new UsuarioController(
            new UsuarioDao($this->conexion),
            $this->renderer
        );
    }

    public function get($objectName)
    {
        return $this->objetos[$objectName];
    }
}
