<?php
include_once __DIR__ . '/../constants/RequestState.php';
class SolicitudPartidaDao
{
    private MyConexion $conexion;
    private UsuarioDao $usuarioDao;

    public function __construct(MyConexion $conexion, UsuarioDao $usuarioDao)
    {
        $this->conexion = $conexion;
        $this->usuarioDao = $usuarioDao;
    }

    public function validarSolicitud($params)
    {

        $idEmisor = (int) trim($params["id_emisor"] ?? 0);
        $idReceptor = (int) trim($params["id_receptor"] ?? 0);

        if (empty($idReceptor) || empty($idEmisor)) {
            return "Los campos no pueden estar vacíos.";
        }

        if ($idEmisor <= 0 || $idReceptor <= 0) {
            return "Los IDs son inválidos.";
        }

        if (!$this->usuarioDao->existsInBd($idEmisor)) {
            return "Alguno de los usuario no se encuentra registrado.";
        }

        if (!$this->usuarioDao->existsInBd($idReceptor)) {
            return "Alguno de los usuario no se encuentra registrado.";
        }

        if ($idEmisor === $idReceptor) {
            return "No puedes enviarte una solicitud a ti mismo.";
        }

        if ($this->existsPendingRequest($idEmisor, $idReceptor)) {
            return "Ya existe una solicitud pendiente entre estos usuarios.";
        }

        $creada = $this->crearSolicitud($idEmisor, $idReceptor) === 1;

        if (!$creada) {
            return "Error al crear la solicitud.";
        }
    }

    public function existsPendingRequest($idEmisor, $idReceptor)
    {
        $sql = "SELECT id FROM solicitud_partida 
        WHERE ((usuario_remitente_id = ? AND usuario_destinatario_id = ?) 
            OR (usuario_remitente_id = ? AND usuario_destinatario_id = ?))
        AND estado_solicitud_id = ?";

        $types = "iiiii";
        $params = [
            $idEmisor,
            $idReceptor,
            $idReceptor,
            $idEmisor,
            RequestState::PENDIENTE
        ];

        $result = $this->conexion->executePrepared($sql, $types, $params);
        $data = $this->conexion->processData($result);

        return !empty($data);
    }

    public function crearSolicitud($idEmisor, $idReceptor)
    {
        $sql = "INSERT INTO solicitud_partida (usuario_remitente_id, usuario_destinatario_id, estado_solicitud_id, fecha_envio) 
        VALUES (?, ?, ?, NOW())";

        $types = "iii";
        $params = [$idEmisor, $idReceptor, RequestState::PENDIENTE];

        return $this->conexion->executePrepared($sql, $types, $params);
    }
}