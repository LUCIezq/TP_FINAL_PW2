<?php

class ReporteAdmin
{
    private MyConexion $db;

    public function __construct(MyConexion $conexion)
    {
        // guardamos la conexión que viene desde el AdminController
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
        $sql = "SELECT COUNT(*) AS total FROM pregunta WHERE estado = 'aprobada'";
        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }

    public function getTotalPreguntasUsuarios(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM pregunta WHERE origen = 'usuario'";
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
        $sql = "SELECT sexo, COUNT(*) AS cantidad FROM usuario GROUP BY sexo";
        return $this->db->query($sql);
    }

    public function getUsuariosPorEdad(): array
    {
        $sql = "
            SELECT 
                CASE
                    WHEN YEAR(CURDATE()) - anio_nacimiento < 18 THEN 'Menores'
                    WHEN YEAR(CURDATE()) - anio_nacimiento >= 65 THEN 'Jubilados'
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
        $sql = "
            SELECT 
                u.nombre_usuario,
                ROUND((SUM(r.correcta) / COUNT(*)) * 100, 1) AS porcentaje
            FROM respuesta r
            INNER JOIN usuario u ON u.id_usuario = r.id_usuario
            GROUP BY u.id_usuario
        ";

        return $this->db->query($sql);
    }
}
