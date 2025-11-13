<?php

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
        /*
        $error_solicitud = $_SESSION['solicitud_errors'];
        $success_solicitud = $_SESSION['solicitud_success'];

        unset($_SESSION['solicitud_errors']);
        unset($_SESSION['solicitud_success']);
        */

        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        if ($_SESSION['user']['rol_id'] == UserRole::JUGADOR) {
            $players = $this->solicitudPartidaDao->allUsersAndRequest($_SESSION['user']['id']);

            $this->mustacheRenderer->render(
                "home",

                [
                    "usuario" => $_SESSION['user'],
                    "isLogged" => $_SESSION['logged_in'],
                    "jugadores" => $players,
                    "isPlayer" => $_SESSION["user"]["rol_id"] === UserRole::JUGADOR
                    /*
                    "solicitud_errors" => $error_solicitud,
                    "solicitud_success" => $success_solicitud,
                    */
                ]
            );
        }
        if ($_SESSION['user']['rol_id'] == UserRole::EDITOR) {
            header("location: /editor/index");
            exit();
        }
    }
}