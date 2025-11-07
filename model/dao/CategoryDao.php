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
}