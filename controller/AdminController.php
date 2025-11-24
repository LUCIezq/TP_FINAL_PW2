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

        $periodo = $_GET['periodo'] ?? 'dia';

    $data = [
        "isDia" => $periodo === "dia",
        "isSemana" => $periodo === "semana",
        "isMes" => $periodo === "mes",
        "isAnio" => $periodo === "anio",

        "totalUsuarios" => $reporte->getTotalUsuarios(),
        "totalPartidas" => $reporte->getTotalPartidas(),
        "totalPreguntas" => $reporte->getTotalPreguntas(),
        "totalPreguntasUsuarios" => $reporte->getTotalPreguntasUsuarios(),

        "usuariosPorPais" => json_encode($reporte->getUsuariosPorPais()),
        "usuariosPorSexo" => json_encode($reporte->getUsuariosPorSexo()),
        "usuariosPorEdad" => json_encode($reporte->getUsuariosPorEdad()),
        "porcentajeCorrectasPorUsuario" => json_encode($reporte->getPorcentajeCorrectasPorUsuario())
    ];

    $this->mustacheRenderer->render("admin", $data);
}
}