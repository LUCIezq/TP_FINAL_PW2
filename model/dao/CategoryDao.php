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

    public function crearCategoria($nombre)
    {
        $sql = "INSERT INTO genero (nombre) VALUES (?)";
        $params = [$nombre];
        $types = "s";

        $result = $this->db->executePrepared($sql, $types, $params);

        if ($result > 0) {
            return "La categoría ha sido creada correctamente.";
        } else {
            return "No se pudo crear la categoría.";
        }
    }

    public function eliminarCategoria($idCategoria)
    {

        $sql = "DELETE FROM genero where id= ?";
        $params = [$idCategoria];
        $types = "i";

        $result = $this->db->executePrepared($sql, $types, $params);

        if ($result > 0) {
            return "La categoría ha sido eliminada correctamente.";
        } else {
            return "No se pudo eliminar la categoría.";
        }
    }

    public function buscarCategoriaPorId($idCategoria)
    {
        $sql = "SELECT * FROM genero WHERE id = ?";
        $params = [$idCategoria];
        $types = "i";

        $result = $this->db->executePrepared($sql, $types, $params);

        return $this->db->processData($result)[0];
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