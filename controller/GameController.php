<?php

class GameController {
    private MustacheRenderer $renderer;
    private GameDao $gameDao;

    public function __construct(MustacheRenderer $renderer, GameDao $gameDao){
        $this->renderer = $renderer;
        $this->gameDao = $gameDao;
    }

    /* ======================
       HOME / RULETA
    ====================== */
    public function base(){
        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        $this->renderer->render("gameRuleta");
    }

    /* ======================
       INICIAR PARTIDA
    ====================== */
    public function start(){
        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        $usuarioId = $_SESSION['user']['id'];
        $message = $_SESSION['message'] ?? null;
        if ($message) unset($_SESSION['message']);

        /* Si había partida en curso → seguir */
        if (isset($_SESSION['partida'])) {

            $p = $_SESSION['partida'];

            $pregunta = $this->gameDao->obtenerPreguntaSimple(
                $p['genero_id'],
                $p['usuario_id']
            );

            if (!$pregunta){
                unset($_SESSION['partida']);
                header("location: /home/index");
                exit();
            }

            $respuestas = $this->gameDao->obtenerRespuestas($pregunta['id']);

            $this->renderer->render("gamePregunta", [
                "partida_id" => $p['id'],
                "pregunta" => $pregunta,
                "respuestas" => $respuestas,
                "usuario_id" => $p['usuario_id'],
                'message' => $message
            ]);

            return;
        }

        /* CREAR PARTIDA NUEVA */
        $generoNombre = $_POST['genero'] ?? null;

        if (!$generoNombre){
            header("location: /game");
            exit();
        }

        $genero = $this->gameDao->obtenerGeneroPorNombre($generoNombre);

        if (!$genero){
            $this->renderer->render("gameRuleta", [
                "mensaje_error" => "No se encontró el género seleccionado."
            ]);
            return;
        }

        $generoId = (int)$genero['id'];

        $partidaId = $this->gameDao->crearPartida(
            $usuarioId,
            $generoId,
            1
        );

        $_SESSION['partida'] = [
            'id' => $partidaId,
            'usuario_id' => $usuarioId,
            'genero_id' => $generoId,
            'preguntas_respondidas' => 0
        ];

        $pregunta = $this->gameDao->obtenerPreguntaSimple($generoId, $usuarioId);

        if (!$pregunta){
            unset($_SESSION['partida']);
            header("location: /home/index");
            exit();
        }

        $respuestas = $this->gameDao->obtenerRespuestas((int)$pregunta['id']);

        $this->renderer->render("gamePregunta", [
            "partida_id" => $partidaId,
            "pregunta" => $pregunta,
            "respuestas" => $respuestas,
            "usuario_id" => $usuarioId,
            "message" => $message
        ]);
    }

    /* ======================
       VALIDAR RESPUESTA
    ====================== */
    public function respuesta(){

        header('Content-Type: application/json');

        if (!IsLogged::isLogged()) {
            echo json_encode(["error" => "No autorizado"]);
            exit();
        }

        $respuestaId = $_POST['respuesta_id'] ?? null;
        $preguntaId  = $_POST['pregunta_id'] ?? null;
        $partidaId   = $_POST['partida_id'] ?? null;
        $usuarioId   = $_SESSION['user']['id'] ?? null;

        if (!$respuestaId || !$preguntaId || !$partidaId || !$usuarioId){
            echo json_encode(["error" => "Datos incompletos"]);
            exit();
        }

        $esCorrecta = $this->gameDao->verificarRespuesta($respuestaId, $preguntaId);

        if ($esCorrecta === null){
            echo json_encode(["error" => "Error al validar"]);
            exit();
        }

        /* guardar historial */
        $this->gameDao->insertarHistorial(
            $usuarioId,
            $partidaId,
            $preguntaId,
            (int)$esCorrecta === 1
        );

        /* respuesta correcta */
        if ((int)$esCorrecta === 1){

            $this->gameDao->sumarPunto($usuarioId);

            $_SESSION['partida']['preguntas_respondidas']++;

            if ($_SESSION['partida']['preguntas_respondidas'] >= 5){

                $this->gameDao->actualizarEstadoPartida($partidaId, "COMPLETADA");
                unset($_SESSION['partida']);

                echo json_encode([
                    "fin" => true,
                    "mensaje" => "🎉 ¡Partida completada!"
                ]);
                exit();
            }

            echo json_encode([
                "correcta" => true,
                "continuar" => true
            ]);
            exit();
        }

        /* respuesta incorrecta */
        $textoCorrecta = $this->gameDao->obtenerRespuestaCorrecta($preguntaId);

        $this->gameDao->actualizarEstadoPartida($partidaId, "PERDIDA");

        unset($_SESSION['partida']);

        echo json_encode([
            "correcta" => false,
            "correcta_texto" => $textoCorrecta
        ]);
    }

    /* ======================
       SIGUIENTE PREGUNTA
    ====================== */
    public function siguientePregunta(){
        if (!IsLogged::isLogged() || !isset($_SESSION['partida'])){
            header("location: /home/index");
            exit();
        }

        $p = $_SESSION['partida'];

        $pregunta = $this->gameDao->obtenerPreguntaSimple(
            $p['genero_id'],
            $p['usuario_id']
        );

        if (!$pregunta){
            unset($_SESSION['partida']);
            header("location: /home/index");
            exit();
        }

        $respuestas = $this->gameDao->obtenerRespuestas((int)$pregunta['id']);

        $this->renderer->render("gamePregunta", [
            "partida_id" => $p['id'],
            "pregunta"   => $pregunta,
            "respuestas" => $respuestas
        ]);
    }
}