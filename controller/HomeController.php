<?php

class HomeController
{
    private MustacheRenderer $mustacheRenderer;
    private UsuarioDao $usuarioDao;
    private SolicitudPartidaDao $solicitudPartidaDao;

    public function __construct(MustacheRenderer $mustacheRenderer, UsuarioDao $usuarioDao, SolicitudPartidaDao $solicitudPartidaDao, GameDao $gameDao)
    {
        $this->mustacheRenderer = $mustacheRenderer;
        $this->usuarioDao = $usuarioDao;
        $this->solicitudPartidaDao = $solicitudPartidaDao;
        $this->gameDao = $gameDao;
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
        // Si existe una partida en curso y el usuario volvió al Home → PERDIDA
        if (isset($_SESSION['partida'])) {
            $partidaId = $_SESSION['partida']['id'];

            // Marcar como perdida
            $this->gameDao->actualizarEstadoPartida($partidaId, "PERDIDA");

            // Borrar sesión
            unset($_SESSION['partida']);
        }


        if ($_SESSION['user']['rol_id'] == UserRole::JUGADOR) {
            $players = $this->solicitudPartidaDao->allUsersAndRequest($_SESSION['user']['id']);
            
            $ranking = $this->usuarioDao->getRankingPromedio();

            foreach ($ranking as $i => &$jugador) {
                $jugador['posicion'] = $i + 1;  

                // Colores especiales
                if ($jugador['posicion'] == 1) $jugador['clase_top'] = "top1";
                elseif ($jugador['posicion'] == 2) $jugador['clase_top'] = "top2";
                elseif ($jugador['posicion'] == 3) $jugador['clase_top'] = "top3";
                else $jugador['clase_top'] = "topN";
            }
            unset($jugador);

            $estadisticas = $this->gameDao->obtenerEstadisticasUsuario($_SESSION['user']['id']);

            $this->mustacheRenderer->render(
                "home",

                [
                    "usuario" => $_SESSION['user'],
                    "isLogged" => $_SESSION['logged_in'],
                    "jugadores" => $players,
                    "ranking_promedio" => ["lista" => $ranking],
                    "estadisticas" => $estadisticas,
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