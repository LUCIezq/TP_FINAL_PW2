<?php
class UsuarioDao
{
    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function createUser($nombre, $apellido, $fecha_nacimiento, $email, $hashedPassword, $nombre_usuario, $foto, $token_verificacion, $sexo_id)
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
        $params = [$nombre, $apellido, $fecha_nacimiento, $email, $hashedPassword, $nombre_usuario, $foto, $token_verificacion, $sexo_id, 2, 1];

        $this->dbConnection->executePrepared($sql, $types, $params);
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

    public function findByEmail($email)
    {
        $sql = "SELECT * FROM usuario WHERE email = ?";
        $types = "s";
        $params = [$email];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result);
    }
}
