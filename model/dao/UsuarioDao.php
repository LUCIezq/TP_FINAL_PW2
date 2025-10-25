<?php
class UsuarioDao
{
    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function createUser($params)
    {
        $sql = "INSERT INTO usuario (
        nombre,
        apellido,
        fecha_nacimiento,
        email,
        contrasena,
        nombre_usuario,
        foto_perfil,
        token_verificacion,
        sexo_id,
        rol_id,
        nivel_id
        ) VALUES ( ?, ?, ?, ?, ?, ?, ? , ? , ? , ? , ? )";

        $types = "ssssssssiii";
        $params = [
            $params['nombre'],
            $params['apellido'],
            $params['fecha_nacimiento'],
            $params['email'],
            $params['contrasena'],
            $params['nombre_usuario'],
            $params['foto_perfil'],
            $params['token_verificacion'],
            $params['sexo_id'],
            $params['rol_id'] ?? 1,
            $params['nivel_id'] ?? 1
        ];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $result === 1;
    }

    public function findByUsername($username)
    {
        $sql = "SELECT * FROM usuario WHERE nombre_usuario = ?";
        $types = "s";
        $params = [$username];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result);
    }

    public function getUserByUsernameOrEmail($username, $email)
    {
        $sql = "SELECT * FROM usuario WHERE nombre_usuario = ? OR email = ?";
        $types = "ss";
        $params = [$username, $email];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result);
    }

    public function getUserByUsername($username)
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

    public function activateUser($username)
    {
        $sql = "UPDATE usuario SET verificado = 1, token_verificacion = NULL WHERE nombre_usuario = ?";
        $types = "s";
        $params = [$username];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $result === 1;
    }
}
