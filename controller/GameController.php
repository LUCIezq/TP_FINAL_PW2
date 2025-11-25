<?php

class GameController {
    private MustacheRenderer $renderer;
    private GameDao $gameDao;

    public function __construct(MustacheRenderer $renderer, GameDao $gameDao){
        $this->renderer = $renderer;
        $this->gameDao = $gameDao;
    }

    public function base(){
        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        $this->renderer->render("gameRuleta");
    }

  public function start(){
    if (!IsLogged::isLogged()) {
        header("location: /login/index");
        exit();
    }

    $usuarioId = $_SESSION['user']['id'];

    unset($_SESSION['partida']);

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

    $tipoDificultad = $this->gameDao->obtenerDificultadIdealUsuario($usuarioId);

    $partidaId = $this->gameDao->crearPartida($usuarioId, $generoId, 1);

    $_SESSION['partida'] = [
        'id' => $partidaId,
        'usuario_id' => $usuarioId,
        'genero_id' => $generoId,
        'preguntas_respondidas' => 0
    ];

    $pregunta = $this->gameDao->obtenerPreguntaSegunDificultad(
        $generoId,
        $usuarioId,
        $tipoDificultad
    );
    if (!$pregunta) {
            $pregunta = $this->gameDao->obtenerPreguntaSimple($generoId, $usuarioId);
        }

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
        "usuario_id" => $usuarioId
    ]);
}

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

        $fueraDeTiempo = $_POST['timeout'] ?? null;

        if (!$respuestaId || !$preguntaId || !$partidaId || !$usuarioId){
            echo json_encode(["error" => "Datos incompletos"]);
            exit();
        }
        if ($fueraDeTiempo) {

            $textoCorrecta = $this->gameDao->obtenerRespuestaCorrecta($preguntaId);

            $this->gameDao->actualizarEstadoPartida($partidaId, "PERDIDA");

            unset($_SESSION['partida']);

            echo json_encode([
                "correcta" => false,
                "correcta_texto" => $textoCorrecta,
                "tiempo_agotado" => true
            ]);
                exit();
        }


        $esCorrecta = $this->gameDao->verificarRespuesta($respuestaId, $preguntaId);

        if ($esCorrecta === null){
            echo json_encode(["error" => "Error al validar"]);
            exit();
        }
        //registrar en historial
        $this->gameDao->insertarHistorial(
            $usuarioId,
            $partidaId,
            $preguntaId,
            (int)$esCorrecta === 1
        );

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

        $textoCorrecta = $this->gameDao->obtenerRespuestaCorrecta($preguntaId);

        $this->gameDao->actualizarEstadoPartida($partidaId, "PERDIDA");

        unset($_SESSION['partida']);

        echo json_encode([
            "correcta" => false,
            "correcta_texto" => $textoCorrecta
        ]);
    }

   public function siguientePregunta(){
    if (!IsLogged::isLogged() || !isset($_SESSION['partida'])){
        header("location: /home/index");
        exit();
    }

    $p = $_SESSION['partida'];

    $tipoDificultad = $this->gameDao->obtenerDificultadIdealUsuario($p['usuario_id']);

    $pregunta = $this->gameDao->obtenerPreguntaSegunDificultad(
        $p['genero_id'],
        $p['usuario_id'],
        $tipoDificultad
    );

    if (!$pregunta) {
            $pregunta = $this->gameDao->obtenerPreguntaSimple($p['genero_id'], $p['usuario_id']);
        }
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