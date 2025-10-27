<?php

include_once "model/Genero.php";

class GeneroDao
{

    private MyConexion $db;
    private Logger $logger;

    public function __construct(MyConexion $dbConexion, Logger $logger)
    {
        $this->db = $dbConexion;
        $this->logger = $logger;
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
            $this->logger->info("Géneros obtenidos correctamente desde la base de datos.");
        }
        return $genders;
    }
}
