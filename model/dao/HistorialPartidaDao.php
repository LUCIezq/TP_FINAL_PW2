<?php

class HistorialPartidaDao
{
    private MyConexion $conexion;

    public function __construct(MyConexion $conexion)
    {
        $this->conexion = $conexion;
    }

    public function insertarHistorial($usuarioId, $partidaId, $preguntaId, $esCorrecta)
    {
        $sql = "INSERT INTO historial_partida 
                (usuario_id, partida_id, pregunta_id, respondida_correctamente)
                VALUES (?, ?, ?, ?)";

        return $this->conexion->executePrepared(
            $sql,
            "iiii",
            [$usuarioId, $partidaId, $preguntaId, $esCorrecta]
        );
    }
}