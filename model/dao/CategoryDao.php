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
        $sql = "SELECT * FROM categoria";
        $result = $this->db->query($sql);

        return $result;
    }
}