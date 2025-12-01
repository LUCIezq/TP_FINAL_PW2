<?php

class NivelDao
{
    private MyConexion $db;

    public function __construct(MyConexion $db)
    {
        $this->db = $db;
    }

    public function obtenerIdNivelSegunRatio($ratio)
    {
        $sql = 'SELECT id
            FROM nivel
            WHERE ? BETWEEN min_ratio AND max_ratio';

        return $this->db->processData(
            $this->db->executePrepared($sql, "d", [$ratio])
        )[0]['id'] ?? null;
    }
}