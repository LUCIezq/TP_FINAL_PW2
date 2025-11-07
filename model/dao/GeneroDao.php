<?php



class GeneroDao
{

    private MyConexion $db;

    public function __construct(MyConexion $dbConexion)
    {
        $this->db = $dbConexion;
    }

    public function getAllGenders()
    {
        $sql = "SELECT * FROM sexo";
        $result = $this->db->query($sql);

        return $result;
    }
}
