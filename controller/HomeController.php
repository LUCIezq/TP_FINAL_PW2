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
        if (isset($_SESSION['partida'])) {

            $partidaId = $_SESSION['partida']['partida_id'];
            $usuarioId = $_SESSION['user']['id'];
            $puntaje = $_SESSION['partida']['puntaje'];
            $ultimoId = $_SESSION['partida']['pregunta_actual_id'];

            $this->gameDao->finalizarPartida($partidaId, $puntaje);
            $this->gameDao->actualizarNivelUsuario($usuarioId);
            $this->gameDao->actualizarDificultadDePregunta($ultimoId);

            unset($_SESSION['partida']);
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
                ]
            );
        }
        if ($_SESSION['user']['rol_id'] == UserRole::EDITOR) {
            header("location: /editor/index");
            exit();
        }

    }

}