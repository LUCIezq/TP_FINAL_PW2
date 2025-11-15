<?php
class ReporteController
{

    private ReporteDao $reporteDao;

    public function __construct(ReporteDao $reporteDao)
    {
        $this->reporteDao = $reporteDao;
    }

    public function reportar()
    {

        if (!IsLogged::isLogged()) {
            header("Location: /login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /");
            exit();
        }

        $motivo = trim($_POST['motivo'] ?? '');
        $detalle = trim($_POST['detalle'] ?? '');
        $pregunta_id = filter_input(INPUT_POST, 'pregunta_id', FILTER_VALIDATE_INT);
        $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);

        if (empty($motivo) || !$pregunta_id || !$usuario_id) {
            $_SESSION['message'] = "Hubo un error al enviar el reporte. Por favor, complete todos los campos.";
            header("Location: /game/start");
            exit();
        }

        try {

            $reporte = new Reporte(
                $motivo,
                $detalle,
                $pregunta_id,
                $usuario_id
            );

            $state = $this->reporteDao->guardarReporte($reporte);
            $_SESSION['message'] = $state;
            header("Location: /game/start");
            exit();

        } catch (Exception $e) {
            $_SESSION['message'] = "Hubo un error al enviar el reporte. Por favor, intente nuevamente." . $e->getMessage();
            header("Location: /game/start");
            exit();
        }
    }

}
