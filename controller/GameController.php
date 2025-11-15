<?php

class GameController
{
    private MustacheRenderer $renderer;
    private GameDao $gameDao;

    public function __construct(MustacheRenderer $renderer, GameDao $gameDao)
    {
        $this->renderer = $renderer;
        $this->gameDao = $gameDao;
    }

    public function base()
    {
        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        $this->renderer->render("gameRuleta");
    }

    public function start()
    {
        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }
        $message = $_SESSION['message'] ?? null;
        if ($message) {
            unset($_SESSION['message']);
        }
        //partida en curso (aca vamos a tener que implentar el tiempo)
        if (isset($_SESSION['partida'])) {
            $partida = $_SESSION['partida'];

            // pasar usuario_id
            $pregunta = $this->gameDao->obtenerPregunta(
                $partida['genero_id'],
                $partida['dificultad_id'],
                $partida['usuario_id']
            );

            $respuestas = $this->gameDao->obtenerRespuestas((int) $pregunta['id']);

            $this->renderer->render("gamePregunta", [
                "partida_id" => $partida['id'],
                "pregunta" => $pregunta,
                "respuestas" => $respuestas,
                "usuario_id" => $partida['usuario_id'],
                'message' => $message
            ]);

            return;
        }

        // sii no hay partida en curso, crear una nueva
        $generoNombre = $_POST['genero'] ?? null;
        if (!$generoNombre) {
            header("location: /game");
            exit();
        }

        $genero = $this->gameDao->obtenerGeneroPorNombre($generoNombre);
        if (!$genero) {
            $this->renderer->render("gameRuleta", [
                "mensaje_error" => "No se encontro el género seleccionado."
            ]);
            return;
        }

        $generoId = (int) $genero['id'];
        $dificultadId = 1; // "inicial" por ahora
        $usuarioId = (int) $_SESSION['user']['id'];

        // crear partida
        $partidaId = $this->gameDao->crearPartida($usuarioId, $generoId, $dificultadId);

        // guardar el progreso en sesion
        $_SESSION['partida'] = [
            'id' => $partidaId,
            'usuario_id' => $usuarioId,
            'genero_id' => $generoId,
            'dificultad_id' => $dificultadId,
            'preguntas_respondidas' => 0,
            'correctas_consecutivas' => 0
        ];

        // obtener primera pregunta sin repetir
        $pregunta = $this->gameDao->obtenerPregunta($generoId, $dificultadId, $usuarioId);
        $respuestas = $this->gameDao->obtenerRespuestas((int) $pregunta['id']);


        $this->renderer->render("gamePregunta", [
            "partida_id" => $partidaId,
            "pregunta" => $pregunta,
            'usuario_id' => $usuarioId,
            "respuestas" => $respuestas,
            'message' => $message
        ]);
    }

    public function respuesta()
    {

        header('Content-Type: application/json');

        if (!IsLogged::isLogged()) {
            echo json_encode(["error" => "No autorizado"]);
            exit();
        }

        $respuestaId = $_POST['respuesta_id'] ?? null;
        $preguntaId = $_POST['pregunta_id'] ?? null;
        $partidaId = $_POST['partida_id'] ?? null;
        $usuarioId = $_SESSION['user']['id'] ?? null;

        if (!$respuestaId || !$preguntaId || !$partidaId || !$usuarioId) {
            echo json_encode(["error" => "Datos incompletos"]);
            exit();
        }

        // 1-verificar si la resp es correcta
        $esCorrecta = $this->gameDao->verificarRespuesta($respuestaId, $preguntaId);

        if ($esCorrecta === null) {
            echo json_encode(["error" => "Error al validar"]);
            exit();
        }

        // 2- registrar en historial
        $this->gameDao->insertarHistorial(
            $usuarioId,
            $partidaId,
            $preguntaId,
            (int) $esCorrecta === 1
        );

        // 3- si es correta:
        if ((int) $esCorrecta === 1) {

            $this->gameDao->sumarPunto($usuarioId);
            $_SESSION['partida']['preguntas_respondidas']++;

            // si llego a 5 preguntas → partida completada (esto lo podemos cambiar)
            if ($_SESSION['partida']['preguntas_respondidas'] >= 5) {
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

        // 3.1 si es incorrecta
        $textoCorrecta = $this->gameDao->obtenerRespuestaCorrecta($preguntaId);
        $this->gameDao->actualizarEstadoPartida($partidaId, "PERDIDA");
        unset($_SESSION['partida']);

        echo json_encode([
            "correcta" => false,
            "correcta_texto" => $textoCorrecta
        ]);
    }

    public function siguientePregunta()
    {

        if (!IsLogged::isLogged() || !isset($_SESSION['partida'])) {
            header("location: /home");
            exit();
        }

        $partida = $_SESSION['partida'];

        // pasamos tambien usuario_id para evitar REPETIDAS
        $pregunta = $this->gameDao->obtenerPregunta(
            $partida['genero_id'],
            $partida['dificultad_id'],
            $partida['usuario_id']
        );

        $respuestas = $this->gameDao->obtenerRespuestas((int) $pregunta['id']);

        $this->renderer->render("gamePregunta", [
            "partida_id" => $partida['id'],
            "pregunta" => $pregunta,
            "respuestas" => $respuestas
        ]);
    }
}