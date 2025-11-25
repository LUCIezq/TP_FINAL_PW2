<?php

class GameDao
{
    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function obtenerGeneroPorNombre($nombre)
    {
        $sql = "SELECT id FROM genero WHERE nombre = ? LIMIT 1";
        $types = "s";
        $params = [$nombre];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);
        $data = $this->dbConnection->processData($result);

        return $data[0] ?? null; // devuelve ['id'=>..] o null
    }

    public function crearPartida($usuarioId, $generoId, $dificultadId)
    {
        $sql = "INSERT INTO partida (usuario_id, genero_actual_id, dificultad_id)
                VALUES (?, ?, ?)";
        $types = "iii";
        $params = [$usuarioId, $generoId, $dificultadId];

        $this->dbConnection->executePrepared($sql, $types, $params);
        return $this->dbConnection->getConnection()->insert_id;
    }

    public function obtenerPregunta($generoId, $dificultadId, $usuarioId)
    {
        $sql = "SELECT p.id, p.texto
            FROM pregunta p
            WHERE p.genero_id = ?
            AND p.dificultad_id = ?
            AND p.estado_id = 1
            AND p.id NOT IN (
                SELECT h.pregunta_id
                FROM historial_partida h
                JOIN pregunta pq ON pq.id = h.pregunta_id
                WHERE h.usuario_id = ?
                    AND pq.genero_id = ?
                    AND pq.dificultad_id = ?
            )
            ORDER BY RAND()
            LIMIT 1";

        $types = "iiiii";
        $params = [$generoId, $dificultadId, $usuarioId, $generoId, $dificultadId];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);
        $data = $this->dbConnection->processData($result);

        return $data[0] ?? null;
    }

    public function obtenerRespuestas($preguntaId)
    {
        $sql = "SELECT id, texto
                FROM respuesta
                WHERE pregunta_id = ?
                ORDER BY RAND()";
        $types = "i";
        $params = [$preguntaId];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);
        return $this->dbConnection->processData($result);
    }

    public function actualizarEstadoPartida($partidaId, $nuevoEstado)
    {
        $sql = "UPDATE partida
                SET estado = ?, ended_at = NOW()
                WHERE id = ?";
        $types = "si";
        $params = [$nuevoEstado, $partidaId];

        return $this->dbConnection->executePrepared($sql, $types, $params);
    }

    public function verificarRespuesta($respuestaId, $preguntaId)
    {
        $sql = "SELECT es_correcta FROM respuesta WHERE id = ? AND pregunta_id = ?";
        $types = "ii";
        $params = [$respuestaId, $preguntaId];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);
        $data = $this->dbConnection->processData($result);

        return $data[0]['es_correcta'] ?? null;
    }
    public function obtenerRespuestaCorrecta($preguntaId)
    {
        $sql = "SELECT texto FROM respuesta WHERE pregunta_id = ? AND es_correcta = 1 LIMIT 1";
        $types = "i";
        $params = [$preguntaId];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);
        $data = $this->dbConnection->processData($result);
        return $data[0]['texto'] ?? '';
    }

    public function insertarHistorial($usuarioId, $partidaId, $preguntaId, $esCorrecta)
    {
        $sql = "INSERT INTO historial_partida (usuario_id, partida_id, pregunta_id, respondida_correctamente)
            VALUES (?, ?, ?, ?)";
        $types = "iiii";
        $params = [$usuarioId, $partidaId, $preguntaId, $esCorrecta ? 1 : 0];

        return $this->dbConnection->executePrepared($sql, $types, $params);
    }

    public function sumarPunto($usuarioId)
    {
        $sql = "UPDATE usuario SET puntos = puntos + 1 WHERE id = ?";
        $types = "i";
        $params = [$usuarioId];
        return $this->dbConnection->executePrepared($sql, $types, $params);
    }

    public function obtenerEstadisticasUsuario($usuarioId)
    {
        $sql = "SELECT 
                COUNT(*) AS partidas_jugadas,
                SUM(CASE WHEN estado = 'COMPLETADA' THEN 1 ELSE 0 END) AS partidas_ganadas,
                SUM(CASE WHEN estado = 'PERDIDA' THEN 1 ELSE 0 END) AS partidas_perdidas
            FROM partida
            WHERE usuario_id = ?";
        $types = "i";
        $params = [$usuarioId];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);
        $data = $this->dbConnection->processData($result);

        return $data[0] ?? [
            'partidas_jugadas' => 0,
            'partidas_ganadas' => 0,
            'partidas_perdidas' => 0
        ];
    }

}