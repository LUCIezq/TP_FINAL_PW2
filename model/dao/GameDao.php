<?php

class GameDao
{
    private MyConexion $dbConnection;
    private CategoryDao $categoryDao;
    private UsuarioDao $usuarioDao;
    private PreguntasDao $preguntaDao;
    private PartidaDao $partidaDao;

    private HistorialPartidaDao $historial;

    public function __construct($dbConnection, CategoryDao $categoryDao, UsuarioDao $usuarioDao, PreguntasDao $preguntasDao, PartidaDao $partidaDao, HistorialPartidaDao $historial)
    {

        $this->dbConnection = $dbConnection;
        $this->categoryDao = $categoryDao;
        $this->usuarioDao = $usuarioDao;
        $this->preguntaDao = $preguntasDao;
        $this->partidaDao = $partidaDao;
        $this->historial = $historial;
    }

    public function finalizarPartida($partidaId, $puntajePartida)
    {
        $sql = 'UPDATE partida 
        set estado_partida_id = ?,
        puntos_alcanzados = ?,
        fecha_fin = NOW()
        WHERE id = ? ';

        $types = 'iii';
        $params = [EstadoPartida::VICTORIA->value, $puntajePartida, $partidaId];

        return $this->dbConnection->executePrepared($sql, $types, $params);
    }


    public function inicializarJuego($genero_id, $user)
    {
        $genero = $this->categoryDao->buscarCategoriaPorId($genero_id);

        if (!$genero)
            throw new Exception("Género no encontrado");

        $usuarioBd = $this->usuarioDao->obtenerUsuarioPorId($user['id']);

        if (!$usuarioBd)
            throw new Exception("Usuario no encontrado");

        $nivelUsuarioId = $this->obtenerNivelIdUsuario($usuarioBd['id']);

        $pregunta = $this->obtenerPreguntaInicial($genero_id, $nivelUsuarioId);

        if (!$pregunta)
            throw new Exception("No hay preguntas disponibles para este género y nivel.");

        $partida_id = $this->partidaDao->crearPartida($usuarioBd['id'], $genero_id);

        $respuestaCorrecta = $this->preguntaDao->obtenerRespuestaCorrectaPorIdPregunta($pregunta['id']);

        return [
            'partida_id' => $partida_id,
            'pregunta_actual_id' => $pregunta['id'],
            'respuesta_correcta_id' => $respuestaCorrecta['id'],
            'preguntas_usadas' => [$pregunta['id']],
            'genero_id' => $genero_id,
            'puntaje' => 0,
            'tiempo_inicio' => time(),
            'tiempo_limite' => 10
        ];
    }

    public function actualizarNivelUsuario($idUsuario)
    {
        return $this->usuarioDao->actualizarNivelUsuario($idUsuario);
    }
    public function obtenerPreguntaInicial($genero_id, $nivelUsuarioId)
    {
        return $this->preguntaDao->obtenerPreguntaInicial($genero_id, $nivelUsuarioId);
    }

    public function obtenerPreguntaParaPartidaEnCurso($genero_id, $nivelUsuarioId, $preguntasUsadas)
    {
        return $this->preguntaDao->obtenerPreguntaParaPartidaEnCurso($genero_id, $nivelUsuarioId, $preguntasUsadas);
    }
    public function obtenerPreguntaPorIdParaPartida($preguntaActualId)
    {
        return $this->preguntaDao->obtenerPreguntaPorIdParaPartida($preguntaActualId);
    }
    public function obtenerRespuestaCorrecta($idPregunta)
    {
        return $this->preguntaDao->obtenerRespuestaCorrecta($idPregunta);
    }
    public function guardarRespuestaEnHistorial($usuarioId, $partidaId, $preguntaId, $esCorrecta)
    {
        return $this->historial->insertarHistorial($usuarioId, $partidaId, $preguntaId, $esCorrecta);
    }
    public function obtenerNivelIdUsuario($usuarioId)
    {
        return $this->usuarioDao->obtenerNivelUsuario($usuarioId);
    }
    public function actualizarDificultadDePregunta($idPregunta)
    {
        return $this->preguntaDao->actualizarDificultadDePregunta($idPregunta);
    }
    public function obtenerRespuestaPorIdRespuesta($idRespuesta)
    {
        return $this->preguntaDao->obtenerRespuestasPorIdRespuesta($idRespuesta);
    }
}
