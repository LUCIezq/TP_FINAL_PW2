<?php

class UsuarioDao
{
    private MyConexion $dbConnection;
    private NivelDao $nivelDao;

    public function __construct($dbConnection, NivelDao $nivelDao)
    {
        $this->dbConnection = $dbConnection;
        $this->nivelDao = $nivelDao;
    }

    public function obtenerNivelUsuario($usuarioId)
    {
        $sql = "SELECT nivel_id FROM usuario WHERE id = ?";
        $types = "i";
        $params = [$usuarioId];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        $data = $this->dbConnection->processData($result);

        return $data[0]['nivel_id'] ?? null;
    }

    public function obtenerPuntosDelUsuario($idUsuario)
    {
        $sql = 'SELECT SUM(puntos_alcanzados) AS total_puntos
            FROM partida 
            WHERE usuario_id = ?';
        $types = 'i';
        $params = [$idUsuario];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        $data = $this->dbConnection->processData($result);


        if (!empty($data)) {
            return $data[0]['total_puntos'] ?? 0;
        }

        return 0;
    }

    public function obtenerCantidadDePreguntasRespondidasPorElUsuario($idUsuario)
    {
        $sql = 'SELECT COUNT(*) as preguntas_respondidas
                FROM historial_partida
                where usuario_id = ?';

        $types = 'i';
        $params = [$idUsuario];

        return $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, $types, $params)
        )[0]['preguntas_respondidas'];
    }

    public function obtenerCantidadDeRespuestasCorrectas($idUsuario)
    {
        $sql = 'SELECT COUNT(*) as preguntas_correctas
                FROM historial_partida
                where usuario_id = ?
                and respondida_correctamente = ?';

        $types = 'ii';
        $params = [$idUsuario, 1];

        return $this->dbConnection->processData(
            $this->dbConnection->executePrepared($sql, $types, $params)
        )[0]['preguntas_correctas'];
    }

    public function calcularRatioDeUsuario($idUsuario)
    {
        $respuestas_correctas = $this->obtenerCantidadDeRespuestasCorrectas($idUsuario);
        $respuestas_jugadas = $this->obtenerCantidadDePreguntasRespondidasPorElUsuario($idUsuario);

        if ($respuestas_jugadas === 0) {
            return 0.0;
        }

        return $respuestas_correctas / $respuestas_jugadas;
    }

    //Este es el metodo que tengo que ejecutar cada vez que el usuario responde
    public function actualizarNivelUsuario($idUsuario)
    {
        $ratio = $this->calcularRatioDeUsuario($idUsuario);
        $nuevoNivelId = $this->nivelDao->obtenerIdNivelSegunRatio($ratio);

        $sql = 'UPDATE usuario 
                SET nivel_id = ?
                WHERE id = ?';

        $types = 'ii';
        $params = [$nuevoNivelId, $idUsuario];

        return $this->dbConnection->executePrepared($sql, $types, $params) > 0;
    }

    public function actualizarPerfil($data)
    {
        $idForm = $data["id"];
        $nombreForm = $data["nombre"];
        $apellidoForm = $data["apellido"];
        $emailForm = $data["email"];
        $usuarioForm = $data["nombre_usuario"];

        $userBd = $this->findById($idForm);

        if (!$userBd) {
            throw new Exception("Usuario no encontrado.");
        }

        $nombreBd = $userBd['nombre'];
        $apellidoBd = $userBd['apellido'];
        $emailBd = $userBd['email'];
        $usuarioBd = $userBd['nombre_usuario'];


        $cambios = [];
        $types = '';
        $params = [];

        if ($nombreForm !== $nombreBd) {
            $cambios[] = 'nombre = ?';
            $types .= 's';
            $params[] = $nombreForm;
        }
        if ($apellidoForm !== $apellidoBd) {
            $cambios[] = 'apellido = ?';
            $types .= 's';
            $params[] = $apellidoForm;
        }
        if ($emailForm !== $emailBd) {
            $yaExisteEmail = $this->findByEmail($emailForm);
            if ($yaExisteEmail) {
                throw new Exception("El email ya está en uso.");
            }
            $cambios[] = 'email = ?';
            $types .= 's';
            $params[] = $emailForm;
        }

        if ($usuarioForm !== $usuarioBd) {
            $yaExisteUsuario = $this->findByUsername($usuarioForm);
            if ($yaExisteUsuario) {
                throw new Exception("El nombre de usuario ya está en uso.");
            }
            $cambios[] = 'nombre_usuario = ?';
            $types .= 's';
            $params[] = $usuarioForm;
        }

        if (empty($params)) {
            throw new Exception("No se realizaron cambios en el perfil.");
        }
        $sql = "UPDATE usuario SET " . implode(", ", $cambios) . " WHERE id = ?";
        $params[] = $idForm;
        $types .= 'i';

        $this->dbConnection->executePrepared($sql, $types, $params);
        return "Perfil actualizado correctamente.";
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

    public function obtenerUsuarioPorId($id)
    {
        $sql = "SELECT * FROM usuario WHERE id = ?";
        $types = "i";
        $params = [$id];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result)[0] ?? null;
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

    public function findById($id)
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
        $params = [$id, UserRole::JUGADOR];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result)[0] ?? null;
    }

    public function existsInBd($id)
    {

        $sql = "SELECT id FROM usuario WHERE id = ?";
        $types = "i";
        $params = [$id];

        $result = $this->dbConnection->executePrepared($sql, $types, $params);

        return $this->dbConnection->processData($result)[0] ?? null;

    }

    public function calcularRankingDeUsuarios()
    {
        $sql = 'SELECT 
                u.id as usuario_id,
                u.nombre,
                u.foto_perfil,
                u.apellido,
                    SUM(h.respondida_correctamente) AS total_aciertos,
                    COUNT(*) AS total_respondidas,
                    ROUND((SUM(h.respondida_correctamente) / COUNT(*))*100,1) AS ratio
                FROM historial_partida h
                JOIN usuario u ON u.id = h.usuario_id
                GROUP BY u.id
                ORDER BY ratio DESC, total_respondidas DESC';

        $data = $this->dbConnection->query($sql);

        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['indice'] = $i + 1;
            }
            return $data;
        }

        return [];
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

    public function sumarPunto($usuarioId, $puntos)
    {
        $sql = "UPDATE usuario 
                SET puntos = puntos + ? 
                WHERE id = ?";
        return $this->dbConnection->executePrepared($sql, "ii", [$puntos, $usuarioId]);
    }
}
