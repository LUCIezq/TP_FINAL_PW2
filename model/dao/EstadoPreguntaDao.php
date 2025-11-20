<?php

class EstadoPreguntaDao
{
    private MyConexion $conexion;

    public function __construct(MyConexion $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerEstadoPorNombre($nombre)
    {
        $sql = "SELECT * FROM estado_pregunta WHERE nombre = :nombre";
        $params = [':nombre' => $nombre];
        $types = 's';
        $result = $this->conexion->executePrepared($sql, $types, $params);
        return $this->conexion->processData($result)[0];
    }

    public function obtenerIdDeEstadoPorNombre($nombre)
    {
        $sql = "SELECT id FROM estado_pregunta WHERE nombre = ?";
        $types = 's';
        $params = [$nombre];

        $result = $this->conexion->processData($this->conexion->executePrepared($sql, $types, $params));
        return (int) $result[0]['id'];
    }
}