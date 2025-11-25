<?php

class AdminController
{
    private MustacheRenderer $mustacheRenderer;
    private MyConexion $conexion;

    public function __construct(MustacheRenderer $mustacheRenderer, MyConexion $conexion)
    {
        $this->mustacheRenderer = $mustacheRenderer;
        $this->conexion = $conexion;
    }
    
    public function index(): void
    {
        /* VALIDACIÓN DE LOGIN */
        if (!IsLogged::isLogged()) {
            header("Location:/login/index");
            exit();
        }

        $role = $_SESSION['user']['rol_id'] ?? null;

        if ($role != UserRole::ADMIN) {
            header("Location:/home/index");
            exit();
        }

        
        require_once __DIR__ . "/../model/ReporteAdmin.php";
        $reporte = new ReporteAdmin($this->conexion);


        /* MANEJO SEGURO DEL PERIODO*/
        $periodo = $_GET['periodo'] ?? 'dia';

        switch ($periodo) {
    case "dia":
        $fechaDesde = date("Y-m-d 00:00:00");
        break;

    case "semana":
        $fechaDesde = date("Y-m-d H:i:s", strtotime("-7 days"));
        break;

    case "mes":
        $fechaDesde = date("Y-m-d H:i:s", strtotime("-1 month"));
        break;

    case "anio":
        $fechaDesde = date("Y-m-d H:i:s", strtotime("-1 year"));
        break;

    default:
        $fechaDesde = date("Y-m-d 00:00:00");
        break;
}



    $data = [
    "isDia" => $periodo === "dia",
    "isSemana" => $periodo === "semana",
    "isMes" => $periodo === "mes",
    "isAnio" => $periodo === "anio",

    "totalUsuarios" => $reporte->getTotalUsuarios($fechaDesde),
    "totalPartidas" => $reporte->getTotalPartidas($fechaDesde),
    "totalPreguntas" => $reporte->getTotalPreguntas(),
    "totalPreguntasUsuarios" => $reporte->getTotalPreguntasUsuarios(),

    // JSON PARA LOS GRÁFICOS
    "usuariosPorPais" => json_encode($reporte->getUsuariosPorPais($fechaDesde)),
    "usuariosPorSexo" => json_encode($reporte->getUsuariosPorSexo($fechaDesde)),
    "usuariosPorEdad" => json_encode($reporte->getUsuariosPorEdad($fechaDesde)),
    "porcentajeCorrectasPorUsuario" => json_encode($reporte->getPorcentajeCorrectasPorUsuario($fechaDesde)),

    // ARRAYS PARA LAS TABLAS
    "usuariosPorPaisTable" => $reporte->getUsuariosPorPais($fechaDesde),
    "usuariosPorSexoTable" => $reporte->getUsuariosPorSexo($fechaDesde),
    "usuariosPorEdadTable" => $reporte->getUsuariosPorEdad($fechaDesde),
    "precisionUsuariosTable" => $reporte->getPorcentajeCorrectasPorUsuario($fechaDesde),
];


        $this->mustacheRenderer->render("admin", $data);
    }
}
