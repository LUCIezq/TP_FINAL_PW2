<?php

class ReporteAdmin
{
    private MyConexion $db;

    public function __construct(MyConexion $conexion)
    {
        $this->db = $conexion;
    }
    
    /*
    Filtro
     */
    private function getWhereFecha($periodo)
    {
        switch ($periodo) {
            case 'dia':
                return "AND fecha >= CURDATE()";
            case 'semana':
                return "AND fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            case 'mes':
                return "AND fecha >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            case 'anio':
                return "AND fecha >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            default:
                return "";
        }
    }

    /*
     *   KPIs(indicadores) PRINCIPALES
     */

    public function getTotalUsuarios($periodo)
    {
        $where = $this->getWhereFecha($periodo);

        $sql = "SELECT COUNT(*) AS total
                FROM usuario
                WHERE 1=1 $where";

        $data = $this->db->query($sql);

        return $data[0]['total'] ?? 0;
    }

    public function getTotalPartidas($periodo)
    {
        $where = $this->getWhereFecha($periodo);

        $sql = "SELECT COUNT(*) AS total
                FROM partida
                WHERE 1=1 $where";

        $data = $this->db->query($sql);

        return $data[0]['total'] ?? 0;
    }

    public function getTotalPreguntas($periodo)
    {
        $where = $this->getWhereFecha($periodo);

        $sql = "SELECT COUNT(*) AS total
                FROM pregunta
                WHERE aprobada = 1 $where";

        $data = $this->db->query($sql);

        return $data[0]['total'] ?? 0;
    }

    public function getTotalPreguntasUsuarios($periodo)
    {
        $where = $this->getWhereFecha($periodo);

        $sql = "SELECT COUNT(*) AS total
                FROM pregunta
                WHERE aprobada = 0 $where";

        $data = $this->db->query($sql);

        return $data[0]['total'] ?? 0;
    }


    /*
    GRÁFICOS (trabajando con arrays)
     */

    public function getUsuariosPorPais($periodo)
    {
        $where = $this->getWhereFecha($periodo);

        $sql = "SELECT pais, COUNT(*) AS total
                FROM usuario
                WHERE 1=1 $where
                GROUP BY pais";

        $data = $this->db->query($sql);

        return $data; // ya es array
    }

    public function getUsuariosPorSexo($periodo)
    {
        $where = $this->getWhereFecha($periodo);

        $sql = "SELECT sexo, COUNT(*) AS total
                FROM usuario
                WHERE 1=1 $where
                GROUP BY sexo";

        $data = $this->db->query($sql);

        return $data;
    }

    public function getUsuariosPorEdad($periodo)
    {
        $where = $this->getWhereFecha($periodo);

        $sql = "SELECT edad, COUNT(*) AS total
                FROM usuario
                WHERE 1=1 $where
                GROUP BY edad";

        $data = $this->db->query($sql);

        return $data;
    }

    public function getPorcentajeCorrectasPorUsuario($periodo)
    {
        $where = $this->getWhereFecha($periodo);

        $sql = "SELECT u.username,
                       AVG(r.correcta) * 100 AS porcentaje
                FROM respuesta r
                JOIN usuario u ON u.id = r.usuario_id
                WHERE 1=1 $where
                GROUP BY u.username";

        $data = $this->db->query($sql);

        return $data;
    }
}