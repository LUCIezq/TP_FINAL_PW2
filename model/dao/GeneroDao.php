<?php



class GeneroDao
{

    private MyConexion $db;

    public function __construct(MyConexion $dbConexion)
    {
        $this->db = $dbConexion;
    }

    public function obtenerGeneroPorNombre($nombre)
    {
        $sql = "SELECT id 
        FROM genero 
        WHERE nombre = ? ";

        return $this->db->processData(
            $this->db->executePrepared($sql, "s", [$nombre])
        )[0] ?? null;
    }

    public function getAllGenders()
    {
        $sql = "SELECT * FROM sexo";
        $result = $this->db->query($sql);

        return $result;
    }
}
