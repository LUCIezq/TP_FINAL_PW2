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

  public function start() {

    if (!IsLogged::isLogged()) {
        header("location: /login/index");
        exit();
    }

        echo "<pre style='background:#222;color:#0f0;padding:15px'>";
        echo 'DEBUG $_SESSION[partida]: ';
        print_r($_SESSION['partida'] ?? 'NO EXISTE');

        echo "\n\nDEBUG POST: ";
        print_r($_POST);

        echo "</pre>";

    $usuarioId = $_SESSION['user']['id'];

    if (isset($_SESSION['partida'])) {

        $p = $_SESSION['partida'];
        $info = $this->gameDao->obtenerPreguntaEnCurso($p['id']);

        if ($info && !empty($info["pregunta_actual_id"])) {

            $preguntaId = $info["pregunta_actual_id"];

            $pregunta = $this->gameDao->obtenerPreguntaPorId($preguntaId);

            $tiempo = $this->gameDao->obtenerTiempoRestante($p['id']);

            if ($tiempo["tiempo_agotado"]) {

                $correcta = $this->gameDao->obtenerRespuestaCorrecta($preguntaId);
                $this->gameDao->actualizarEstadoPartida($p['id'], "PERDIDA");
                unset($_SESSION['partida']);

                $this->renderer->render("gameTiempoVencido", [
                    "correcta" => $correcta
                ]);
                return;
            }

            $respuestas = $this->gameDao->obtenerRespuestas($preguntaId);

            $this->renderer->render("gamePregunta", [
                "partida_id" => $p['id'],
                "pregunta" => $pregunta,
                "respuestas" => $respuestas,
                "usuario_id" => $usuarioId,
                "tiempo_restante" => $tiempo["segundos_restantes"]
            ]);
            return;
        }
    }

    $generoNombre = $_POST['genero'] ?? null;

    if (!$generoNombre) {
        header("location: /game");
        exit();
    }

    $genero = $this->gameDao->obtenerGeneroPorNombre($generoNombre);

    if (!$genero) {
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
        $generoId, $usuarioId, $tipoDificultad
    );

    if (!$pregunta) {
        $pregunta = $this->gameDao->obtenerPreguntaSimple($generoId, $usuarioId);
    }

    if (!$pregunta) {
        unset($_SESSION['partida']);
        header("location: /home/index");
        exit();
    }

    $this->gameDao->marcarInicioPregunta($partidaId, $pregunta['id']);

    $tiempo = $this->gameDao->obtenerTiempoRestante($partidaId);

    if ($tiempo["tiempo_agotado"]) {

        $textoCorrecta = $this->gameDao->obtenerRespuestaCorrecta($pregunta['id']);
        $this->gameDao->actualizarEstadoPartida($partidaId, "PERDIDA");
        unset($_SESSION['partida']);

        $this->renderer->render("gameTiempoVencido", [
            "correcta" => $textoCorrecta
        ]);
        return;
    }

    $respuestas = $this->gameDao->obtenerRespuestas($pregunta['id']);

    $this->renderer->render("gamePregunta", [
        "partida_id" => $partidaId,
        "pregunta" => $pregunta,
        "respuestas" => $respuestas,
        "usuario_id" => $usuarioId,
        "tiempo_restante" => $tiempo["segundos_restantes"]
    ]);
}


    public function respuesta() {
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

    //$tiempo = $this->gameDao->obtenerTiempoRestante($partidaId);

    /*if ($tiempo["tiempo_agotado"]) {

        $textoCorrecta = $this->gameDao->obtenerRespuestaCorrecta($preguntaId);
        $this->gameDao->actualizarEstadoPartida($partidaId, "PERDIDA");

        unset($_SESSION['partida']);

        echo json_encode([
            "correcta" => false,
            "correcta_texto" => $textoCorrecta,
            "tiempo_agotado" => true
        ]);
        exit();
    }*/

    $esCorrecta = $this->gameDao->verificarRespuesta($respuestaId, $preguntaId);

    if ($esCorrecta === null){
        echo json_encode(["error" => "Error al validar"]);
        exit();
    }

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


 public function siguientePregunta()
{
    if (!IsLogged::isLogged() || !isset($_SESSION['partida'])) {
        header("location: /home/index");
        exit();
    }

    $p = $_SESSION['partida'];

    $info = $this->gameDao->obtenerPreguntaEnCurso($p['id']);

    if ($info && !empty($info["pregunta_actual_id"])) {

        $preguntaId = $info["pregunta_actual_id"];
        $pregunta = $this->gameDao->obtenerTextoPregunta($preguntaId);

        $tiempo = $this->gameDao->obtenerTiempoRestante($p['id']);

        if ($tiempo["tiempo_agotado"]) {
            $correcta = $this->gameDao->obtenerRespuestaCorrecta($preguntaId);
            $this->gameDao->actualizarEstadoPartida($p['id'], "PERDIDA");
            unset($_SESSION['partida']);

            $this->renderer->render("gameTiempoVencido", [
                "correcta" => $correcta
            ]);
            return;
        }

        $respuestas = $this->gameDao->obtenerRespuestas($preguntaId);

        $this->renderer->render("gamePregunta", [
            "partida_id" => $p['id'],
            "pregunta" => $pregunta,
            "respuestas" => $respuestas,
            "tiempo_restante" => $tiempo["segundos_restantes"]
        ]);

        return;
    }

    $tipoDificultad = $this->gameDao->obtenerDificultadIdealUsuario($p['usuario_id']);

    $pregunta = $this->gameDao->obtenerPreguntaSegunDificultad(
        $p['genero_id'],
        $p['usuario_id'],
        $tipoDificultad
    );

    if (!$pregunta) {
        $pregunta = $this->gameDao->obtenerPreguntaSimple($p['genero_id'], $p['usuario_id']);
    }

    if (!$pregunta) {
        unset($_SESSION['partida']);
        header("location: /home/index");
        exit();
    }

    $this->gameDao->marcarInicioPregunta($p['id'], $pregunta['id']);

    $tiempo = $this->gameDao->obtenerTiempoRestante($p['id']);

    $respuestas = $this->gameDao->obtenerRespuestas($pregunta['id']);

    $this->renderer->render("gamePregunta", [
        "partida_id" => $p['id'],
        "pregunta" => $pregunta,
        "respuestas" => $respuestas,
        "tiempo_restante" => $tiempo["segundos_restantes"]
    ]);
}

}