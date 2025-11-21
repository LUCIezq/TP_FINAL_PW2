<?php

class EstadisticasDao{
    private $db;

    public function __construct($dbConnection){
        $this->db = $dbConnection;
    }

    public function obtenerEstadisticasPreguntas(){
        $sql = "SELECT 
                p.id,
                p.texto,
                p.genero_id,
                COUNT(h.id) AS total_respuestas,
                SUM(h.respondida_correctamente) AS total_correctas,
                CASE 
                    WHEN COUNT(h.id) = 0 THEN 0
                    ELSE (SUM(h.respondida_correctamente) / COUNT(h.id)) * 100
                END AS porcentaje_acierto
            FROM pregunta p
            LEFT JOIN historial_partida h ON h.pregunta_id = p.id
            GROUP BY p.id";

        $result = $this->db->executePrepared($sql);
        return $this->db->processData($result);
    }

   
    public function obtenerRatioPregunta($preguntaId){
        $sql = "SELECT 
                COUNT(h.id) AS total_respuestas,
                SUM(h.respondida_correctamente) AS total_correctas,
                CASE 
                    WHEN COUNT(h.id) = 0 THEN 0
                    ELSE (SUM(h.respondida_correctamente) / COUNT(h.id)) * 100
                END AS porcentaje_acierto
            FROM historial_partida h
            WHERE h.pregunta_id = ?";

        $result = $this->db->executePrepared($sql, "i", [$preguntaId]);
        $data = $this->db->processData($result);
        return $data[0] ?? null;
    }

 
    public function obtenerRatioUsuario($usuarioId){
        $sql = "SELECT 
                COUNT(h.id) AS total_respondidas,
                SUM(h.respondida_correctamente) AS total_correctas,
                CASE 
                    WHEN COUNT(h.id) = 0 THEN 0
                    ELSE (SUM(h.respondida_correctamente) / COUNT(h.id)) * 100
                END AS porcentaje_acierto
            FROM historial_partida h
            WHERE h.usuario_id = ?";

        $result = $this->db->executePrepared($sql, "i", [$usuarioId]);
        $data = $this->db->processData($result);
        return $data[0] ?? null;
    }

    public function obtenerEstadisticasPartidasUsuario($usuarioId){
        $sql = "SELECT 
                COUNT(*) AS partidas_jugadas,
                SUM(CASE WHEN estado = 'COMPLETADA' THEN 1 ELSE 0 END) AS partidas_ganadas,
                SUM(CASE WHEN estado = 'PERDIDA' THEN 1 ELSE 0 END) AS partidas_perdidas
            FROM partida
            WHERE usuario_id = ?";

        $result = $this->db->executePrepared($sql, "i", [$usuarioId]);
        $data = $this->db->processData($result);
        return $data[0] ?? null;
    }
}
