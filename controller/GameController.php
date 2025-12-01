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
    public function ruleta()
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
            header('location:/login/index');
            exit();
        }

        if (isset($_SESSION['partida'])) {
            $this->cargarPartidaEnCurso();
            return;
        }

        $genero_id = $this->obtenerGeneroId();

        if (!$genero_id) {
            header('location:/game/ruleta');
            exit();
        }

        try {
            $data = $this->gameDao->inicializarJuego($genero_id, $_SESSION['user']);
            $_SESSION['partida'] = $data;

            header('location:/game/start');
            exit();

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    private function cargarPartidaEnCurso()
    {
        $partida = $_SESSION['partida'];

        $preguntaId = $partida['pregunta_actual_id'];
        $pregunta = $this->gameDao->obtenerPreguntaPorIdParaPartida($preguntaId);

        $transcurrido = time() - $partida['tiempo_inicio'];

        $this->renderer->render("gamePregunta", [
            "pregunta" => $pregunta,
            "usuario" => $_SESSION['user'],
            "tiempo_restante" => $partida['tiempo_limite'] - $transcurrido,
            "tiempo_limite" => $partida['tiempo_limite'],
            "partida_id" => $partida['partida_id']
        ]);
    }

    private function obtenerGeneroId()
    {
        $options = [
            "options" => [
                "min_range" => 1,
                "max_range" => PHP_INT_MAX
            ]
        ];

        $idSanitizado = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_NUMBER_INT);
        return filter_var($idSanitizado, FILTER_VALIDATE_INT, $options);
    }

    public function responder()
    {
        if (!IsLogged::isLogged()) {
            return SendJSON::procesarJSON([
                "status" => "no_auth",
                "redirect" => "/login/index"
            ]);
        }

        if (!isset($_SESSION['partida'])) {
            return SendJSON::procesarJSON([
                "status" => "no_game",
                "redirect" => "/home/index"
            ]);
        }

        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        if (!isset($data['pregunta_id'])) {
            return SendJSON::procesarJSON([
                "status" => "invalid_request",
                "message" => "Datos incompletos."
            ]);
        }

        $preguntaId = (int) $data['pregunta_id'];
        $respuestaId = $data['respuesta_id'] ?? null;

        if ($respuestaId === null) {
            if ($this->tiempoExpirado()) {

                $correcta = $_SESSION['partida']['respuesta_correcta_id'];

                $this->finalizarPartidaSinRedirect();

                return SendJSON::procesarJSON([
                    "status" => "time_out",
                    "correcta" => $correcta,
                    'mensaje' => 'Tiempo agotado',
                    'redirect' => '/home/index'
                ]);
            }
        }

        $respuestaId = (int) $respuestaId;

        $this->validarPreguntaYRespuesta($preguntaId, $respuestaId);

        $esCorrecta = $respuestaId === $_SESSION['partida']['respuesta_correcta_id'];

        $this->gameDao->guardarRespuestaEnHistorial($_SESSION['user']['id'], $_SESSION['partida']['partida_id'], $preguntaId, $esCorrecta ? 1 : 0);

        if (!$esCorrecta) {
            $correcta = $_SESSION['partida']['respuesta_correcta_id'];

            $this->finalizarPartidaSinRedirect();
            return SendJSON::procesarJSON([
                "status" => "incorrecta",
                "correcta" => $correcta,
                "respuesta_usuario" => $respuestaId
            ]);
        }
        $this->procesarRespuestaCorrecta();
    }
    private function tiempoExpirado()
    {
        $inicio = $_SESSION['partida']['tiempo_inicio'];
        $limite = $_SESSION['partida']['tiempo_limite'];

        return time() - $inicio >= $limite;
    }
    private function validarPreguntaYRespuesta($idPregunta, $idRespuesta)
    {
        if (!is_numeric($idPregunta) || !is_numeric($idRespuesta)) {
            SendJSON::procesarJSON(["mensaje" => "Datos inválidos."]);
        }

        if ($idPregunta != $_SESSION['partida']['pregunta_actual_id']) {
            SendJSON::procesarJSON(["mensaje" => "Pregunta fuera de contexto."]);
        }

        $respuesta = $this->gameDao->obtenerRespuestaPorIdRespuesta($idRespuesta);

        if (!$respuesta) {
            SendJSON::procesarJSON(["mensaje" => "Respuesta inexistente."]);
        }

        if ($respuesta['pregunta_id'] != $idPregunta) {
            SendJSON::procesarJSON(["mensaje" => "La respuesta no corresponde a la pregunta."]);
        }
    }
    private function procesarRespuestaCorrecta()
    {
        $usuarioId = $_SESSION['user']['id'];
        $preguntaId = $_SESSION['partida']['pregunta_actual_id'];
        $correctaAnterior = $_SESSION['partida']['respuesta_correcta_id'];
        $_SESSION['partida']['puntaje']++;

        $this->gameDao->actualizarDificultadDePregunta($preguntaId);

        $nivelUsuario = $this->gameDao->obtenerNivelIdUsuario($usuarioId);

        $nuevaPregunta = $this->gameDao->obtenerPreguntaParaPartidaEnCurso(
            $_SESSION['partida']['genero_id'],
            $nivelUsuario,
            $_SESSION['partida']['preguntas_usadas']
        );

        if (!$nuevaPregunta) {
            $this->finalizarPartidaSinRedirect();

            return SendJSON::procesarJSON([
                "status" => "no_more_questions",
                'mensaje' => '¡Increíble! 🎉 Has respondido a TODAS las preguntas de esta categoría!',
                "correcta" => $correctaAnterior
            ]);
        }

        $respuestaCorrecta = $this->gameDao->obtenerRespuestaCorrecta($nuevaPregunta['id']);

        $_SESSION['partida']['pregunta_actual_id'] = $nuevaPregunta['id'];
        $_SESSION['partida']['respuesta_correcta_id'] = $respuestaCorrecta['id'];
        $_SESSION['partida']['tiempo_inicio'] = time();
        $_SESSION['partida']['preguntas_usadas'][] = $nuevaPregunta['id'];

        return SendJSON::procesarJSON([
            'status' => 'correcta',
            'mensaje' => 'correcta',
            'siguiente_pregunta' => $nuevaPregunta,
            "correcta" => $correctaAnterior,
            'preguntas_usadas' => $_SESSION['partida']['preguntas_usadas']
        ]);
    }
    private function finalizarPartidaSinRedirect()
    {
        if (!isset($_SESSION['partida'])) {
            return;
        }

        $partidaId = $_SESSION['partida']['partida_id'];
        $usuarioId = $_SESSION['user']['id'];
        $puntaje = $_SESSION['partida']['puntaje'];
        $ultimoId = $_SESSION['partida']['pregunta_actual_id'];

        $this->gameDao->finalizarPartida($partidaId, $puntaje);
        $this->gameDao->actualizarNivelUsuario($usuarioId);
        $this->gameDao->actualizarDificultadDePregunta($ultimoId);

        unset($_SESSION['partida']);
    }
}
