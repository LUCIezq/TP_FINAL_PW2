<?php
class ReporteController
{

    private ReporteDao $reporteDao;
    private MustacheRenderer $mustacheRenderer;
    private PreguntasDao $preguntasDao;

    public function __construct(ReporteDao $reporteDao, MustacheRenderer $mustacheRenderer, PreguntasDao $preguntasDao)
    {
        $this->reporteDao = $reporteDao;
        $this->mustacheRenderer = $mustacheRenderer;
        $this->preguntasDao = $preguntasDao;
    }

    public function index()
    {
        $message = $_SESSION["message"];
        unset($_SESSION["message"]);

        $this->mustacheRenderer->render("reporte", [$message => $message]);
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

    public function ver()
    {
        //aca deberiamos validar que este logueado y sea editor

        $id_pregunta = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id_pregunta || $id_pregunta <= 0) {
            header("Location: /editor/index");
            exit();
        }

        $pregunta = $this->preguntasDao->obtenerPreguntaPorId($id_pregunta);

        if (!$pregunta) {
            header("Location: /editor/index");
            exit();
        }

        $preguntaConReportes = $this->reporteDao->obtenerReportesPorIdPregunta($id_pregunta);

        $this->mustacheRenderer->render("reporte", [
            'preguntaConReportes' => $preguntaConReportes,
            'pregunta' => $pregunta
        ]);
    }

}
