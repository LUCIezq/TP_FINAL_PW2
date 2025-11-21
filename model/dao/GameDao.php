<?php

class GameDao {
    private $dbConnection;

    public function __construct($dbConnection){
        $this->dbConnection = $dbConnection;
    }

    /* ========================
       GENERO
    ======================== */
    public function obtenerGeneroPorNombre($nombre){
        $sql = "SELECT id FROM genero WHERE nombre = ? LIMIT 1";
        return $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, "s", [$nombre])
        )[0] ?? null;
    }

    /* ========================
       RESPUESTAS
    ======================== */
    public function obtenerRespuestas($preguntaId){
        $sql = "SELECT id, texto 
                FROM respuesta 
                WHERE pregunta_id = ? 
                ORDER BY RAND()";

        return $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, "i", [$preguntaId])
        );
    }

    /* ========================
       PARTIDA
    ======================== */
    public function crearPartida($usuarioId, $generoId, $dificultadId){
        $sql = "INSERT INTO partida (usuario_id, genero_actual_id, dificultad_id)
                VALUES (?, ?, ?)";
        $this->dbConnection->executePrepared($sql, "iii", [$usuarioId, $generoId, $dificultadId]);

        return $this->dbConnection->getConnection()->insert_id;
    }

    public function actualizarEstadoPartida($partidaId, $nuevoEstado){
        $sql = "UPDATE partida 
                SET estado = ?, ended_at = NOW() 
                WHERE id = ?";

        return $this->dbConnection->executePrepared($sql, "si", [$nuevoEstado, $partidaId]);
    }

    /* ========================
       PREGUNTAS (VERSIÓN SIMPLE ESTABLE)
    ======================== */
    public function obtenerPreguntaSimple($generoId, $usuarioId){

        // Primero: sin repetir
        $sql = "SELECT id, texto 
                FROM pregunta
                WHERE genero_id = ?
                  AND id NOT IN (
                        SELECT pregunta_id 
                        FROM historial_partida 
                        WHERE usuario_id = ?
                  )
                ORDER BY RAND()
                LIMIT 1";

        $data = $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, "ii", [$generoId, $usuarioId])
        );

        if (!empty($data)) return $data[0];

        // Si no quedan nuevas → permitir repetidas
        $sql2 = "SELECT id, texto
                 FROM pregunta
                 WHERE genero_id = ?
                 ORDER BY RAND()
                 LIMIT 1";

        $data2 = $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql2, "i", [$generoId])
        );

        return $data2[0] ?? null;
    }

    /* ========================
       VALIDACIÓN DE RESPUESTAS
    ======================== */
    public function verificarRespuesta($respuestaId, $preguntaId){
        $sql = "SELECT es_correcta 
                FROM respuesta 
                WHERE id = ? AND pregunta_id = ?";

        $data = $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, "ii", [$respuestaId, $preguntaId])
        );

        return $data[0]['es_correcta'] ?? null;
    }

    public function obtenerRespuestaCorrecta($preguntaId){
        $sql = "SELECT texto 
                FROM respuesta 
                WHERE pregunta_id = ? AND es_correcta = 1 
                LIMIT 1";

        $data = $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, "i", [$preguntaId])
        );

        return $data[0]['texto'] ?? '';
    }

    /* ========================
       HISTORIAL
    ======================== */
    public function insertarHistorial($usuarioId, $partidaId, $preguntaId, $esCorrecta){
        $sql = "INSERT INTO historial_partida 
                (usuario_id, partida_id, pregunta_id, respondida_correctamente)
                VALUES (?, ?, ?, ?)";

        return $this->dbConnection->executePrepared(
            $sql,
            "iiii",
            [$usuarioId, $partidaId, $preguntaId, $esCorrecta ? 1 : 0]
        );
    }

    /* ========================
       PUNTAJE
    ======================== */
    public function sumarPunto($usuarioId){
        $sql = "UPDATE usuario 
                SET puntos = puntos + 1 
                WHERE id = ?";

        return $this->dbConnection->executePrepared($sql, "i", [$usuarioId]);
    }

    /* ========================
       ESTADISTICAS
    ======================== */
    public function obtenerEstadisticasUsuario($usuarioId){
        $sql = "SELECT 
                    COUNT(*) AS partidas_jugadas,
                    SUM(CASE WHEN estado = 'COMPLETADA' THEN 1 ELSE 0 END) AS partidas_ganadas,
                    SUM(CASE WHEN estado = 'PERDIDA' THEN 1 ELSE 0 END) AS partidas_perdidas
                FROM partida
                WHERE usuario_id = ?";

        $data = $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, "i", [$usuarioId])
        );

        return $data[0] ?? [
            'partidas_jugadas' => 0,
            'partidas_ganadas' => 0,
            'partidas_perdidas' => 0
        ];
    }
}
