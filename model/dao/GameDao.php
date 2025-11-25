<?php

class GameDao {
    private $dbConnection;

    public function __construct($dbConnection){
        $this->dbConnection = $dbConnection;
    }

    public function obtenerGeneroPorNombre($nombre){
        $sql = "SELECT id FROM genero WHERE nombre = ? LIMIT 1";
        return $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, "s", [$nombre])
        )[0] ?? null;
    }

    public function obtenerRespuestas($preguntaId){
        $sql = "SELECT id, texto 
                FROM respuesta 
                WHERE pregunta_id = ? 
                ORDER BY RAND()";

        return $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, "i", [$preguntaId])
        );
    }

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

   public function obtenerPreguntaSimple($generoId, $usuarioId)
   {
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

    public function obtenerPreguntaSegunDificultad($generoId, $usuarioId, $tipoDificultad) {
        if ($tipoDificultad === "dificil") {
            $filtro = "IFNULL(estad.ratio, 100) < 30";
        } elseif ($tipoDificultad === "medio") {
            $filtro = "IFNULL(estad.ratio, 100) BETWEEN 30 AND 70";
        } else { 
            $filtro = "IFNULL(estad.ratio, 100) > 70";
        }

        $sql = "SELECT p.id, p.texto
                FROM pregunta p
                LEFT JOIN (
                    SELECT pregunta_id,
                        (SUM(respondida_correctamente) / COUNT(*)) * 100 AS ratio
                    FROM historial_partida
                    GROUP BY pregunta_id
                ) estad ON p.id = estad.pregunta_id
                WHERE p.genero_id = ?
                AND $filtro
                AND p.id NOT IN (
                        SELECT pregunta_id 
                        FROM historial_partida
                        WHERE usuario_id = ?
                )
                ORDER BY RAND()
                LIMIT 1";

        $result = $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, "ii", [$generoId, $usuarioId])
        );

        if (!empty($result)) return $result[0];

        $sql2 = "SELECT p.id, p.texto
                FROM pregunta p
                LEFT JOIN (
                    SELECT pregunta_id,
                        (SUM(respondida_correctamente) / COUNT(*)) * 100 AS ratio
                    FROM historial_partida
                    GROUP BY pregunta_id
                ) estad ON p.id = estad.pregunta_id
                WHERE p.genero_id = ?
                AND $filtro
                ORDER BY RAND()
                LIMIT 1";

        $result2 = $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql2, "i", [$generoId])
        );

        return $result2[0] ?? null;
    }
   
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
                WHERE pregunta_id = ? 
                AND es_correcta = 1 
                LIMIT 1";

        $data = $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, "i", [$preguntaId])
        );

        return $data[0]['texto'] ?? '';
    }

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

    public function sumarPunto($usuarioId){
        $sql = "UPDATE usuario 
                SET puntos = puntos + 1 
                WHERE id = ?";
        return $this->dbConnection->executePrepared($sql, "i", [$usuarioId]);
    }

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

    public function obtenerDificultadIdealUsuario($usuarioId) {
        $sql = "SELECT 
                    COUNT(*) AS total_respondidas,
                    SUM(respondida_correctamente) AS total_correctas
            FROM historial_partida
            WHERE usuario_id = ?";

        $data = $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, "i", [$usuarioId])
        );
        $total = $data[0]["total_respondidas"] ?? 0;
        $correctas = $data[0]["total_correctas"] ?? 0;

        if ($total == 0) return 'facil';

        $ratio = ($correctas / $total) * 100;

        if ($ratio > 70) return 'dificil';
        if ($ratio >= 30) return 'medio';
            return 'facil';
    }
}
