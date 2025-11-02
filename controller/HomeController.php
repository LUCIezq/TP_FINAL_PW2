<?php

include_once 'helper/IsLogged.php';

class HomeController
{
    private MustacheRenderer $mustacheRenderer;
    private UsuarioDao $usuarioDao;
    private SolicitudPartidaDao $solicitudPartidaDao;

    public function __construct(MustacheRenderer $mustacheRenderer, UsuarioDao $usuarioDao, SolicitudPartidaDao $solicitudPartidaDao)
    {
        $this->mustacheRenderer = $mustacheRenderer;
        $this->usuarioDao = $usuarioDao;
        $this->solicitudPartidaDao = $solicitudPartidaDao;
    }

    public function index(): void
    {
        $error_solicitud = $_SESSION['solicitud_errors'];
        $success_solicitud = $_SESSION['solicitud_success'];

        unset($_SESSION['solicitud_errors']);
        unset($_SESSION['solicitud_success']);

        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        $players = $this->solicitudPartidaDao->allUsersAndRequest($_SESSION['user']['id']);

        $this->mustacheRenderer->render(
            "home",

            [
                "usuario" => $_SESSION['user']['nombre_usuario'],
                "url_profile" => $_SESSION['user']['foto_perfil'],
                "isLogged" => $_SESSION['logged_in'],
                "jugadores" => $players,
                "solicitud_errors" => $error_solicitud,
                "solicitud_success" => $success_solicitud,
                "id" => $_SESSION["user"]["id"]
            ]
        );
    }
}