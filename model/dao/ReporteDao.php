<?php

class ReporteDao
{
    private MyConexion $db;
    private const CANTIDAD_MAXIMA_REPORTE = 1;
    private PreguntasDao $preguntasDao;
    public function __construct(MyConexion $db, PreguntasDao $preguntasDao)
    {
        $this->db = $db;
        $this->preguntasDao = $preguntasDao;
    }

    public function getAllReportes()
    {
        $sql = "SELECT r.id_reporte,count(r.id_reporte) as cantidad_reportes,
        p.id as pregunta_id,
        p.texto as enunciado,
        u.nombre_usuario as usuario,
        er.nombre,
        r.motivo,
        r.comentario,
        r.fecha_reporte,
        er.nombre as reporte_nombre
        FROM reporte r
        JOIN usuario u on u.id = r.id_usuario
        JOIN estado_reporte er on er.id_estado_reporte = r.id_estado_reporte
        JOIN pregunta p on p.id = r.id_pregunta
        where r.id_estado_reporte = 1
        GROUP BY p.id";
        $result = $this->db->executePrepared($sql, "", []);
        return $this->db->processData($result);
    }

    public function aprobarReporte($idPregunta)
    {

        $sql = 'UPDATE reporte 
        set id_estado_reporte = ?
        WHERE id_pregunta = ?';

        $types = 'is';

        $params = [2, $idPregunta];

        return $this->db->executePrepared($sql, $types, $params) > 0;
    }

    public function obtenerReportesPorIdPregunta($idPregunta)
    {
        $sql = 'SELECT *,usuario.nombre_usuario from reporte
        JOIN usuario ON reporte.id_usuario = usuario.id 
        WHERE id_pregunta = ?';

        $params = [$idPregunta];

        $types = "i";

        $result = $this->db->executePrepared($sql, $types, $params);
        return $this->db->processData($result);
    }

    public function rechazarReporte($idPregunta)
    {

        $sql = 'UPDATE reporte 
        set id_estado_reporte = ?
        WHERE id_pregunta = ?';

        $types = 'is';

        $params = [3, $idPregunta];

        return $this->db->executePrepared($sql, $types, $params) > 0;
    }

    public function guardarReporte(Reporte $reporte)
    {

        $preguntaBd = $this->preguntasDao->obtenerPreguntaPorId($reporte->getPreguntaId());

        if (!$preguntaBd) {
            return 'La pregunta no existe.';
        }

        $existeReportePorPreguntaYUsuario = $this->existeReportePorUsuarioYPreguntaPendiente($reporte->getUsuarioId(), $reporte->getPreguntaId());

        if (count($existeReportePorPreguntaYUsuario) > 0) {
            return 'Ya has enviado un reporte para esta pregunta.';
        }

        $sql = "INSERT INTO reporte (id_pregunta,id_usuario,id_estado_reporte,motivo,comentario,fecha_reporte) 
                VALUES (?, ?, ?, ?, ?,current_timestamp())";
        $params = [
            $reporte->getPreguntaId(),
            $reporte->getUsuarioId(),
            EstadoReporte::PENDIENTE,
            $reporte->getMotivo(),
            $reporte->getDetalle()
        ];

        $types = "ssiss";

        $result = $this->db->executePrepared($sql, $types, $params) > 0;

        $cantidadDeReportes = $this->obtenerCantidadDeReportesPorIdDePregunta($reporte->getPreguntaId());

        if ($cantidadDeReportes >= self::CANTIDAD_MAXIMA_REPORTE) {
            $this->preguntasDao->inactivarPregunta($reporte->getPreguntaId());
        }

        return $result ? 'Reporte guardado con éxito.' : 'Error al guardar el reporte.';
    }

    public function existeReportePorUsuarioYPreguntaPendiente($usuario_id, $pregunta_id)
    {
        $sql = "SELECT * FROM reporte
        WHERE id_usuario = ? AND id_pregunta = ? and id_estado_reporte = ?";

        $params = [$usuario_id, $pregunta_id, EstadoReporte::PENDIENTE];
        $types = "iii";

        $result = $this->db->executePrepared($sql, $types, $params);
        return $this->db->processData($result);
    }

    public function obtenerCantidadDeReportesPorIdDePregunta($pregunta_id)
    {

        $sql = "SELECT COUNT(*) AS total FROM reporte
        WHERE id_pregunta = ?";

        $params = [$pregunta_id];
        $types = "i";

        $result = $this->db->executePrepared($sql, $types, $params);
        return $this->db->processData($result)[0]['total'];
    }
}