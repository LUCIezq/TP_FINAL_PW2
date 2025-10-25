<?php

class MyConexion
{
    private $conexion;
    private $logger;

    public function __construct($server, $user, $password, $database, $logger)
    {
        $this->logger = $logger;

        $this->conexion = new mysqli($server, $user, $password, $database);

        if ($this->conexion->connect_error) {
            $this->logger->error("Error de conexión: " . $this->conexion->connect_error);
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
            return null;
        }
        return $result;
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
        return $stmt->affected_rows;
    }

    public function close()
    {
        $this->conexion ?? $this->conexion->close();
    }

    public function validateResult($result)
    {
        if ($result === false) {
            $this->logger->info("Hubo un error al ejecutar la consulta en la base de datos.");
            return false;
        }
        if ($result->num_rows === 0) {
            $this->logger->info("No se encontraron registros en la base de datos.");
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
