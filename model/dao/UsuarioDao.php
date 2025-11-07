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
        nivel_id,
        pais,
        ciudad
        ) VALUES ( ?, ?, ?, ?, ?, ?, ? , ? , ? , ?,?, ?, ? )";

        $types = "ssssssssiiiss";
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
            $params['nivel_id'] ?? 1,
            $params['pais'],
            $params['ciudad'],
        ];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $result === 1;
    }

    public function getCountryAndCityById($id)
    {
        $sql = 'SELECT pais,ciudad from usuario where id=?';
        $types = 'i';
        $params = [$id];

        $data = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($data)[0] ?? null;
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

        return $this->dbConnection->processData($result)[0];
    }

    public function activateUser($username)
    {
        $sql = "UPDATE usuario SET verificado = 1, token_verificacion = NULL, token_expiracion = CURRENT_TIMESTAMP WHERE nombre_usuario = ?";
        $types = "s";
        $params = [$username];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $result === 1;
    }

    public function updateUserToken($username, $token)
    {
        $sql = "UPDATE usuario SET token_verificacion = ? WHERE nombre_usuario = ?";
        $types = "ss";
        $params = [$token, $username];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $result === 1;
    }

    public function getAllPlayers($userId)
    {
        $sql = "SELECT id,foto_perfil,nombre,apellido,nombre_usuario FROM usuario where rol_id = ? and id != ? and verificado = true";
        $types = "ii";
        $params = [UserRole::JUGADOR, $userId];
        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result);
    }

    public function findById($id) //cambiar este metodo 🔋🔋🔋🔋🔋🔋
    {
        $sql = "SELECT id,nombre,apellido,email,nombre_usuario,foto_perfil,nivel_id,puntos FROM usuario WHERE id = ? and rol_id = ?";
        $types = "ii";
        $params = [$id, UserRole::JUGADOR];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result)[0] ?? null;
    }

    /*
     * 🔋🔋🔋🔋🔋🔋 metodo que funciona bien, agregando nuevos campos
     * public function findById($id)
    {
        $sql = "SELECT
                id,
                nombre,
                apellido,
                email,
                nombre_usuario,
                foto_perfil,
                nivel_id,
                puntos
            FROM usuario
            WHERE id = ? AND rol_id = ?";
        $types = "ii";
        $params = [$id, 1];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result)[0] ?? null;
    }
     */
    public function existsInBd($id)
    {

        $sql = "SELECT id FROM usuario WHERE id = ?";
        $types = "i";
        $params = [$id];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result)[0] ?? null;

    }

    public function getAllUserAndRequests($userLoggedId)
    {
        if (!$userLoggedId || $userLoggedId <= 0) {
            return [];
        }

        $sql = "SELECT  u.id as id_usuario,
        u.foto_perfil,
        u.nombre,
        u.apellido,
        u.nombre_usuario,
        sp.id as id_solicitud,
        sp.usuario_remitente_id ,
        sp.usuario_destinatario_id,
        sp.estado_solicitud_id,
        timestampdiff(MINUTE, sp.fecha_envio, NOW()) as minutos_desde_solicitud

        from usuario u left join solicitud_partida sp

        on((sp.usuario_remitente_id = ? and sp.usuario_destinatario_id = u.id)||
        (sp.usuario_destinatario_id = ? and sp.usuario_remitente_id = u.id)) and sp.estado_solicitud_id in (1,2)

        where u.rol_id = ? 
        and u.id != ? 
        and u.verificado = 1";

        $types = "iiii";
        $params = [$userLoggedId, $userLoggedId, UserRole::JUGADOR, $userLoggedId];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result);
    }

    public function getConnection()
    {
        return $this->dbConnection;
    }

}
