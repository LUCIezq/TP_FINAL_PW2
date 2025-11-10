<?php

class CategoryDao
{

    private MyConexion $db;

    public function __construct(MyConexion $dbConexion)
    {
        $this->db = $dbConexion;
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM genero";
        $result = $this->db->query($sql);

        return $result;
    }

    public function getById(int $id): array
    {
        $sql = "SELECT nombre FROM genero WHERE id = ?";
        $params = [$id];
        $types = "i";

        $result = $this->db->executePrepared($sql, $types, $params);

        return $this->db->processData($result)[0];

    }
}