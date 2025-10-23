<?php
class UsuarioDao
{
    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function findByUsername($username)
    {
        $sql = "SELECT * FROM usuario WHERE nombre_usuario = ?";
        $types = "s";
        $params = [$username];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result);
    }

    public function findByEmail($email)
    {
        $sql = "SELECT * FROM usuario WHERE email = ?";
        $types = "s";
        $params = [$email];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result);
    }
}
