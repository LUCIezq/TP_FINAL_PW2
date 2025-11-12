<?php

class MyConexion
{
    private $conexion;

    public function __construct($server, $user, $password, $database)
    {

        $this->conexion = new mysqli($server, $user, $password, $database);
        $this->conexion->set_charset("utf8mb4");

        if ($this->conexion->connect_error) {
            throw new Exception("Error de conexión: " . $this->conexion->connect_error);
        }
    }

    public function getConnection()
    {
        return $this->conexion;
    }

    public function query($sql)
    {
        $result = $this->conexion->query($sql);
        if (!$result || $result->num_rows <= 0) {
            return [];
        }
        return $this->processData($result);
    }

    public function executePrepared($sql, $types, $params)
    {
        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conexion->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        if (str_starts_with(strtoupper(trim($sql)), 'SELECT')) {
            return $stmt->get_result();
        }

        if (str_starts_with(strtoupper(trim($sql)), 'INSERT')) {
            return $stmt->insert_id;
        }

        return $stmt->affected_rows;
    }
    public function close()
    {
        $this->conexion ?? $this->conexion->close();
    }

    public function validateResult($result)
    {
        if ($result === false) {
            return false;
        }
        if ($result->num_rows === 0) {
            return false;
        }

        return true;
    }

    public function processData($result)
    {
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}
