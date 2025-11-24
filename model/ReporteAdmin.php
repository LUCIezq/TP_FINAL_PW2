<?php

class ReporteAdmin
{
    private MyConexion $db;

    public function __construct(MyConexion $conexion)
    {
        $this->db = $conexion;
    }

    // =====================================================
    //   FECHAS COMPATIBLES CON INFINITYFREE
    // =====================================================
    public function fechaDesde(string $filtro): string
    {
        switch ($filtro) {
            case "hoy":     
                return date("Y-m-d");
            case "semana":  
                return date("Y-m-d", strtotime("-7 days"));
            case "mes":     
                return date("Y-m-d", strtotime("-1 month"));
            case "anio":    
                return date("Y-m-d", strtotime("-1 year"));
            default:
                return "1970-01-01";
        }
    }

    // =====================================================
    //                     MÉTRICAS
    // =====================================================

    public function getTotalUsuarios(string $fechaDesde): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM usuario
            WHERE DATE(fecha_creacion) >= '$fechaDesde'
        ";

        $result = $this->db->query($sql);
        return $result[0]["total"] ?? 0;
    }

    public function getTotalPartidas(string $fechaDesde): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM partida
            WHERE DATE(created_at) >= '$fechaDesde'
        ";

        $result = $this->db->query($sql);
        return $result[0]["total"] ?? 0;
    }

    public function getTotalPreguntas(): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM pregunta
            WHERE estado_id = 1
        ";

        $result = $this->db->query($sql);
        return $result[0]["total"] ?? 0;
    }

    public function getTotalPreguntasUsuarios(string $fechaDesde): int
    {
        // No filtramos por fecha, pero si querés se agrega DATE(fecha_creacion)
        $sql = "
            SELECT COUNT(*) AS total
            FROM pregunta
            WHERE usuario_id NOT IN (1,2)
        ";

        $result = $this->db->query($sql);
        return $result[0]["total"] ?? 0;
    }

    // =====================================================
    //               GRÁFICOS
    // =====================================================

    public function getUsuariosPorPais(string $fechaDesde): array
    {
        $sql = "
            SELECT 
                CASE 
                    WHEN pais IS NULL OR pais = '' THEN 'Sin especificar'
                    ELSE pais
                END AS pais,
                COUNT(*) AS cantidad
            FROM usuario
            WHERE DATE(fecha_creacion) >= '$fechaDesde'
            GROUP BY pais
        ";

        return $this->db->query($sql);
    }

    public function getUsuariosPorSexo(string $fechaDesde): array
    {
        $sql = "
            SELECT 
                CASE 
                    WHEN s.nombre IS NULL THEN 'Sin especificar'
                    ELSE s.nombre
                END AS sexo,
                COUNT(*) AS cantidad
            FROM usuario u
            LEFT JOIN sexo s ON u.sexo_id = s.id
            WHERE DATE(u.fecha_creacion) >= '$fechaDesde'
            GROUP BY sexo
        ";

        return $this->db->query($sql);
    }

    public function getUsuariosPorEdad(string $fechaDesde): array
    {
        $sql = "
            SELECT 
                CASE
                    WHEN fecha_nacimiento IS NULL THEN 'Sin especificar'
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) < 18 THEN 'Menores'
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) >= 65 THEN 'Jubilados'
                    ELSE 'Adultos'
                END AS grupo,
                COUNT(*) AS cantidad
            FROM usuario
            WHERE DATE(fecha_creacion) >= '$fechaDesde'
            GROUP BY grupo
        ";

        return $this->db->query($sql);
    }

    public function getPorcentajeCorrectasPorUsuario(string $fechaDesde): array
    {
        $sql = "
            SELECT 
                u.nombre_usuario,
                ROUND((SUM(h.respondida_correctamente) / COUNT(*)) * 100, 1) AS porcentaje
            FROM historial_partida h
            INNER JOIN usuario u ON u.id = h.usuario_id
            WHERE DATE(h.fecha_respuesta) >= '$fechaDesde'
            GROUP BY h.usuario_id
        ";

        return $this->db->query($sql);
    }
}
