<?php

class HomeController
{
    private MustacheRenderer $mustacheRenderer;
    private UsuarioDao $usuarioDao;
    private SolicitudPartidaDao $solicitudPartidaDao;
    private GameDao $gameDao;

    public function __construct(MustacheRenderer $mustacheRenderer, UsuarioDao $usuarioDao, SolicitudPartidaDao $solicitudPartidaDao, GameDao $gameDao)
    {
        $this->mustacheRenderer = $mustacheRenderer;
        $this->usuarioDao = $usuarioDao;
        $this->solicitudPartidaDao = $solicitudPartidaDao;
        $this->gameDao = $gameDao;
    }

    public function index(): void
    {

        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        if ($_SESSION['user']['rol_id'] == UserRole::JUGADOR) {

            $players = $this->solicitudPartidaDao->allUsersAndRequest($_SESSION['user']['id']);

            $ranking = $this->usuarioDao->calcularRankingDeUsuarios();

            $this->mustacheRenderer->render(
                "home",

                [
                    "usuario" => $_SESSION['user'],
                    "isLogged" => $_SESSION['logged_in'],
                    "jugadores" => $players,
                    'ranking' => $ranking,
                    "isPlayer" => $_SESSION["user"]["rol_id"] === UserRole::JUGADOR
                ]
            );
        }
        if ($_SESSION['user']['rol_id'] == UserRole::EDITOR) {
            header("location: /editor/index");
            exit();
        }

    }

}