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

        // =============================
        //     FILTRO DE TIEMPO
        // =============================
        // valores posibles: dia, semana, mes, anio
        $periodo = $_GET['periodo'] ?? 'dia';

        // convertir periodo a fechaDesde
        switch ($periodo) {
            case "dia":     $fechaDesde = $reporte->fechaDesde("hoy"); break;
            case "semana":  $fechaDesde = $reporte->fechaDesde("semana"); break;
            case "mes":     $fechaDesde = $reporte->fechaDesde("mes"); break;
            case "anio":    $fechaDesde = $reporte->fechaDesde("anio"); break;
            default:        $fechaDesde = $reporte->fechaDesde("hoy");
        }

        // =============================
        //          MÉTRICAS
        // =============================

        $data = [
    "isDia" => $periodo === "dia",
    "isSemana" => $periodo === "semana",
    "isMes" => $periodo === "mes",
    "isAnio" => $periodo === "anio",

    "totalUsuarios" => $reporte->getTotalUsuarios($fechaDesde),
    "totalPartidas" => $reporte->getTotalPartidas($fechaDesde),
    "totalPreguntas" => $reporte->getTotalPreguntas(),
    "totalPreguntasUsuarios" => $reporte->getTotalPreguntasUsuarios($fechaDesde),

    // TABLAS (ARRAYS)
    "usuariosPorPais" => $reporte->getUsuariosPorPais($fechaDesde),
    "usuariosPorSexo" => $reporte->getUsuariosPorSexo($fechaDesde),
    "usuariosPorEdad" => $reporte->getUsuariosPorEdad($fechaDesde),
    "porcentajeCorrectasPorUsuario" => $reporte->getPorcentajeCorrectasPorUsuario($fechaDesde),

    // GRÁFICOS (JSON)
    "usuariosPorPaisJson" => json_encode($reporte->getUsuariosPorPais($fechaDesde)),
    "usuariosPorSexoJson" => json_encode($reporte->getUsuariosPorSexo($fechaDesde)),
    "usuariosPorEdadJson" => json_encode($reporte->getUsuariosPorEdad($fechaDesde)),
    "porcentajeCorrectasPorUsuarioJson" => json_encode($reporte->getPorcentajeCorrectasPorUsuario($fechaDesde))
];


        $this->mustacheRenderer->render("admin", $data);
    }
}
