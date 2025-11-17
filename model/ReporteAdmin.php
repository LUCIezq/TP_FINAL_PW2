<?php

class ReporteAdmin
{
    private MyConexion $db;

    public function __construct(MyConexion $conexion)
    {
        $this->db = $conexion;
    }

    // =============================
    //     KPI PRINCIPALES
    // =============================

    public function getTotalUsuarios(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM usuario";
        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }

    public function getTotalPartidas(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM partida";
        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }

    public function getTotalPreguntas(): int
    {
        // Todas las preguntas activas
        $sql = "SELECT COUNT(*) AS total FROM pregunta WHERE activa = 1";
        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }

    public function getTotalPreguntasUsuarios(): int
    {
        // Preguntas creadas por usuarios (todas lo son)
        $sql = "SELECT COUNT(*) AS total FROM pregunta";
        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }

    // =============================
    //          GRÁFICOS
    // =============================

    public function getUsuariosPorPais(): array
    {
        $sql = "SELECT pais, COUNT(*) AS cantidad FROM usuario GROUP BY pais";
        return $this->db->query($sql);
    }

    public function getUsuariosPorSexo(): array
    {
        $sql = "
            SELECT s.nombre AS sexo, COUNT(*) AS cantidad
            FROM usuario u
            LEFT JOIN sexo s ON u.sexo_id = s.id
            GROUP BY sexo_id
        ";
        return $this->db->query($sql);
    }

    public function getUsuariosPorEdad(): array
    {
        $sql = "
            SELECT 
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) < 18 THEN 'Menores'
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) >= 65 THEN 'Jubilados'
                    ELSE 'Adultos'
                END AS grupo,
                COUNT(*) AS cantidad
            FROM usuario
            GROUP BY grupo
        ";

        return $this->db->query($sql);
    }

    public function getPorcentajeCorrectasPorUsuario(): array
    {
        // Se calcula desde historial_partida
        $sql = "
            SELECT 
                u.nombre_usuario,
                ROUND((SUM(h.respondida_correctamente) / COUNT(*)) * 100, 1) AS porcentaje
            FROM historial_partida h
            INNER JOIN usuario u ON u.id = h.usuario_id
            GROUP BY h.usuario_id
        ";

        return $this->db->query($sql);
    }
}
