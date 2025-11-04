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

        $genders = [];

        if ($this->db->validateResult($result)) {
            while ($row = $result->fetch_assoc()) {
                $genders[] = [
                    'id' => $row['id'],
                    'nombre' => $row['nombre']
                ];
            }
        }
        return $genders;
    }
}
