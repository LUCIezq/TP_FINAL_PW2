<?php

class ReporteAdmin
{
    private MyConexion $db;

    public function __construct(MyConexion $conexion)
    {
        $this->db = $conexion;
    }

    /*  Validacion para fechas*/
    private function safeDate(?string $fecha): string
    {
        if (!$fecha || trim($fecha) === "" || strtolower($fecha) === "null") {
            return "1970-01-01 00:00:00"; // fecha mínima segura
        }
        return $fecha;
    }



    public function getTotalUsuarios(string $fechaDesde): int
    {
        $fecha = $this->safeDate($fechaDesde);

        $sql = "SELECT COUNT(*) AS total 
                FROM usuario 
                WHERE fecha_creacion >= '$fecha'";

        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }

    public function getTotalPartidas(string $fechaDesde): int
    {
        $fecha = $this->safeDate($fechaDesde);

        $sql = "SELECT COUNT(*) AS total 
                FROM partida 
                WHERE created_at >= '$fecha'";

        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }

    public function getTotalPreguntas(): int
    {
        $sql = "SELECT COUNT(*) AS total 
                FROM pregunta 
                WHERE estado_id = 1";

        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }

    public function getTotalPreguntasUsuarios(): int
    {
        $sql = "SELECT COUNT(*) AS total 
                FROM pregunta";

        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }


    /* GRÁFICOS */

    public function getUsuariosPorPais(string $fechaDesde): array
    {
        $fecha = $this->safeDate($fechaDesde);

        $sql = "
            SELECT 
                CASE WHEN pais IS NULL OR pais = '' THEN 'Sin especificar'
                     ELSE pais END AS pais,
                COUNT(*) AS cantidad
            FROM usuario
            WHERE fecha_creacion >= '$fecha'
            GROUP BY pais
        ";

        return $this->db->query($sql);
    }

    public function getUsuariosPorSexo(string $fechaDesde): array
    {
        $fecha = $this->safeDate($fechaDesde);

        $sql = "
            SELECT 
                CASE WHEN s.nombre IS NULL THEN 'Sin especificar'
                     ELSE s.nombre END AS sexo,
                COUNT(*) AS cantidad
            FROM usuario u
            LEFT JOIN sexo s ON u.sexo_id = s.id
            WHERE u.fecha_creacion >= '$fecha'
            GROUP BY sexo
        ";

        return $this->db->query($sql);
    }

    public function getUsuariosPorEdad(string $fechaDesde): array
    {
        $fecha = $this->safeDate($fechaDesde);

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
            WHERE fecha_creacion >= '$fecha'
            GROUP BY grupo
        ";

        return $this->db->query($sql);
    }

    public function getPorcentajeCorrectasPorUsuario(string $fechaDesde): array
    {
        $fecha = $this->safeDate($fechaDesde);

        $sql = "
            SELECT 
                u.nombre_usuario,
                ROUND((SUM(h.respondida_correctamente) / COUNT(*)) * 100, 1) AS porcentaje
            FROM historial_partida h
            INNER JOIN usuario u ON u.id = h.usuario_id
            WHERE h.fecha_respuesta >= '$fecha'
            GROUP BY h.usuario_id
        ";

        return $this->db->query($sql);
    }
}
