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
        // Verificar login
        if (!IsLogged::isLogged()) {
            header("Location:/login/index");
            exit();
        }

        // Verificar rol admin
        $role = $_SESSION['user']['rol_id'] ?? null;

        if ($role != UserRole::ADMIN) {
            header("Location:/home/index");
            exit();
        }

        // Cargar el modelo con la conexión REAL del sistema
        require_once __DIR__ . "/../model/ReporteAdmin.php";
        $reporte = new ReporteAdmin($this->conexion);

        //Obtener datos para el dashboard
        $data = [
            "totalUsuarios"           => $reporte->getTotalUsuarios(),
            "totalPartidas"           => $reporte->getTotalPartidas(),
            "totalPreguntas"          => $reporte->getTotalPreguntas(),
            "totalPreguntasUsuarios"  => $reporte->getTotalPreguntasUsuarios(),

            // Gráficos
            "usuariosPorPais"         => $reporte->getUsuariosPorPais(),
            "usuariosPorSexo"         => $reporte->getUsuariosPorSexo(),
            "usuariosPorEdad"         => [],
            "porcentajeCorrectas"     => []
        ];

        // Renderizar la vista del dashboard
        $this->mustacheRenderer->render("adminVista", $data);
    }
}