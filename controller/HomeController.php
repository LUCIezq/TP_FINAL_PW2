<?php

include_once 'helper/IsLogged.php';
include_once 'helper/StartSession.php';


class HomeController
{
    private MustacheRenderer $mustacheRenderer;
    private UsuarioDao $usuarioDao;

    public function __construct(MustacheRenderer $mustacheRenderer, UsuarioDao $usuarioDao)
    {
        $this->mustacheRenderer = $mustacheRenderer;
        $this->usuarioDao = $usuarioDao;
    }

    public function index(): void
    {

        StartSession::start();

        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        $players = $this->usuarioDao->getAllPlayers($_SESSION['user']['id']);

        $this->mustacheRenderer->render(
            "home",

            [
                "usuario" => $_SESSION['user']['nombre_usuario'],
                "url_profile" => $_SESSION['user']['foto_perfil'],
                "isLogged" => $_SESSION['logged_in'],
                "jugadores" => $players,
            ]
        );
    }
}