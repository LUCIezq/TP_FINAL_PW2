<?php

class PartidaDao
{
    private MyConexion $conexion;

    public function __construct(MyConexion $conexion)
    {
        $this->conexion = $conexion;
    }

    public function crearPartida($usuarioId, $generoId)
    {
        $sql = "INSERT INTO partida 
        (usuario_id, genero_actual_id,estado_partida_id)
        VALUES (?,?,?)";

        return $this->conexion->executePrepared($sql, "iii", [$usuarioId, $generoId, EstadoPartida::EN_PROGRESO->value]);
    }

    public function actualizarEstadoPartida($partidaId, $estadoId)
    {
        $sql = "UPDATE partida 
                SET estado_partida_id = ? 
                WHERE id = ?";

        return $this->conexion->executePrepared($sql, "ii", [$estadoId, $partidaId]);
    }

    public function obtenerEstadisticasDePartidasPorUsuario($usuarioId)
    {
        $sql = "SELECT 
            g.nombre as genero_nombre,
            p.puntos_alcanzados as puntos,
            TIMEDIFF(p.fecha_fin,p.fecha_inicio) as duracion
        
        FROM partida p 
        JOIN genero g ON p.genero_id = g.id
        
        WHERE usuario_id = ?
        ORDER BY p.puntos_alcanzados DESC
        LIMIT 3
        ";

        $data = $this->conexion->processData(
            $this->conexion->executePrepared($sql, "i", [$usuarioId])
        );

        foreach ($data as $i => &$row) {
            $row['posicion'] = $i + 1;
            $row['genero_nombre'] = strtoupper($row['genero_nombre'][0]) . substr($row['genero_nombre'], 1);
        }

        return $data ?? [
            'genero_nombre' => '',
            'puntos' => 0,
            'duracion' => 0,
            'posicion' => 0
        ];
    }

    public function obtenerGenerosMasJugadosPorUsuario($usuarioId)
    {
        $sql = "SELECT 
        g.id,
        g.nombre,
        COUNT(p.id) AS veces_jugado
        
        FROM partida p
        JOIN genero g ON p.genero_id = g.id
        WHERE p.usuario_id = ?
        GROUP BY g.id, g.nombre
        ORDER BY veces_jugado DESC;";


        $data = $this->conexion->processData(
            $this->conexion->executePrepared($sql, "i", [$usuarioId])
        );

        $vecesJugado = $this->obtenerCantidadDePartidasJugadas($usuarioId);

        if ($vecesJugado == 0)
            $vecesJugado = 1;

        foreach ($data as $i => &$row) {
            $row['porcentaje'] = round($row['veces_jugado'] / $vecesJugado * 100, 1);
            $row['nombre'] = strtoupper($row['nombre'][0]) . substr($row['nombre'], 1);
            $row['indice'] = $i + 1;
        }

        return $data ?? [];

    }

    public function obtenerCantidadDePartidasJugadas($usuarioId)
    {
        $sql = "SELECT 
        COUNT(*) AS partidas_jugadas
        FROM partida
        WHERE usuario_id = ?;";

        $data = $this->conexion->processData(
            $this->conexion->executePrepared($sql, "i", [$usuarioId])
        );

        return $data[0]['partidas_jugadas'] ?? 0;
    }

    public function obtenerTotalDePreguntasRespondidas($usuarioId)
    {
        $sql = 'SELECT COUNT(*) AS total_respondidas
        FROM historial_partida h
        WHERE h.usuario_id = ?;';

        $data = $this->conexion->processData(
            $this->conexion->executePrepared($sql, "i", [$usuarioId])
        );
        return $data[0]['total_respondidas'] ?? 0;
    }

    public function obtenerTotalDePreguntasCorrectas($usuarioId)
    {
        $sql = 'SELECT COUNT(*) AS total_correctas
        FROM historial_partida h
        WHERE h.usuario_id = ? AND h.respondida_correctamente = 1;';

        $data = $this->conexion->processData(
            $this->conexion->executePrepared($sql, "i", [$usuarioId])
        );
        return $data[0]['total_correctas'] ?? 0;
    }

    public function calcularPromedioDeAcierto($usuarioId)
    {
        $totalRespondidas = $this->obtenerTotalDePreguntasRespondidas($usuarioId);
        $totalCorrectas = $this->obtenerTotalDePreguntasCorrectas($usuarioId);

        if ($totalRespondidas === 0) {
            return 0;
        }

        return round($totalCorrectas / $totalRespondidas * 100, 2);
    }

}