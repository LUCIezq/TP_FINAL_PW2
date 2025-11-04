<?php

include_once 'vendor/mustache/src/Mustache/Autoloader.php';

class ConfigFactory
{
    private $config;
    private $objetos;

    private $conexion;
    private $renderer;

    public function __construct()
    {

        $env = ($_SERVER['SERVER_NAME'] === 'localhost') ? 'local' : 'prod';
        $path = __DIR__ . '/../config/configDB.' . $env . '.ini';

        if (!file_exists($path)) {
            throw new Exception('Archivo no encontrado');
        }
        $this->config = parse_ini_file($path, true);

        $this->conexion = new MyConexion(
            $this->config["server"],
            $this->config["user"],
            $this->config["password"],
            $this->config["database"]
        );

        $this->renderer = new MustacheRenderer("vista");

        $this->objetos["router"] = new NewRouter($this, "PokemonController", "base");

        $this->objetos["RegisterController"] = new RegisterController(
            new GeneroDao($this->conexion),
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

        $this->objetos['PreguntasController'] = new PreguntasController(
            $this->renderer,
            $this->conexion,
            new CategoryDao($this->conexion)
        );

        $this->objetos['CategoriaController'] = new CategoriaController(
            new CategoryDao($this->conexion)
        );


    }

    public function get($objectName)
    {
        return $this->objetos[$objectName];
    }
}
