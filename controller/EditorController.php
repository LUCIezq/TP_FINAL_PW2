<?php
class EditorController
{

    private EditorDao $dao;
    private MustacheRenderer $mustacheRenderer;
    private PreguntasDao $preguntasDao;
    private CategoryDao $categoryDao;

    private ReporteDao $reporteDao;

    public function __construct(EditorDao $dao, MustacheRenderer $mustacheRenderer, PreguntasDao $preguntasDao, CategoryDao $categoryDao, ReporteDao $reporteDao)
    {
        $this->dao = $dao;
        $this->mustacheRenderer = $mustacheRenderer;
        $this->preguntasDao = $preguntasDao;
        $this->categoryDao = $categoryDao;
        $this->reporteDao = $reporteDao;
    }

    public function index()
    {

        if (!IsLogged::isLogged()) {
            header("Location:/login/index");
            exit();
        }

        $role = $_SESSION['user']['rol_id'] ?? null;

        if ($role != UserRole::EDITOR) {
            header("Location:/home/index");
            exit();
        }

        $message = $_SESSION["message"] ?? null;
        unset($_SESSION["message"]);


        $opcionesMenu = $this->obtenerOpcionesDashboard();
        $preguntasDelSistema = $this->preguntasDao->getAllSystemQuestions();
        $preguntasSugeridas = $this->preguntasDao->obtenerPreguntasSugeridas();
        $categorias = $this->categoryDao->getAll();
        $preguntasReportadas = $this->reporteDao->getAllReportes();


        $this->mustacheRenderer->render("editor", [
            'preguntasDelSistema' => $preguntasDelSistema,
            "isLogged" => IsLogged::isLogged(),
            "categories" => $categorias,
            'preguntasSugeridas' => $preguntasSugeridas,
            'preguntasReportadas' => $preguntasReportadas,
            "message" => $message,
            "usuario" => $_SESSION['user'],
            'opcionesMenu' => $opcionesMenu
        ]);
    }

    private function obtenerOpcionesDashboard()
    {

        return [
            [
                'ancla' => 'crearPregunta',
                'titulo' => 'Crear pregunta',
                'descripcion' => 'Cargar nueva pregunta al sistema.'
            ],
            [
                'ancla' => 'preguntasSistema',
                'titulo' => 'Preguntas del sistema',
                'descripcion' => 'Ver y gestionar las preguntas existentes en el sistema.'
            ],
            [
                'ancla' => 'preguntasSugeridas',
                'titulo' => 'Preguntas sugeridas',
                'descripcion' => 'Revisar y aprobar o rechazar preguntas sugeridas por usuarios.'
            ],
            [
                'ancla' => 'preguntasReportadas',
                'titulo' => 'Preguntas reportadas',
                'descripcion' => 'Revisar y gestionar preguntas que han sido reportadas por usuarios.'
            ],
            [
                'ancla' => 'categorias',
                'titulo' => 'Categorías',
                'descripcion' => 'Gestionar las categorías de las preguntas.'
            ]
        ];
    }

    public function eliminarCategoria()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $idCategoria = filter_input(INPUT_POST, 'idCategoria', FILTER_VALIDATE_INT);

            if (!$idCategoria) {
                $_SESSION['message'] = "ID de categoría inválido.";
                header("Location:/editor/index");
                exit();
            }
            try {
                $preguntas = $this->preguntasDao->getPreguntasPorCategoria($idCategoria);

                if (count($preguntas) > 0) {
                    $_SESSION['message'] = "No se puede eliminar la categoría porque tiene preguntas asociadas.";
                    header("Location:/editor/index");
                    exit();
                }

                $state = $this->categoryDao->eliminarCategoria($idCategoria);

                $_SESSION['message'] = $state;
                header("Location:/editor/index");
                exit();

            } catch (Exception $e) {
                $_SESSION["message"] = "Error al eliminar la categoría.";
                header("Location:/editor/index");
                exit();
            }
        }
    }

    public function crearCategoria()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $nombre = htmlspecialchars(trim($_POST["nombre"]), ENT_QUOTES, 'UTF-8');

            if (empty($nombre)) {
                $_SESSION["message"] = "El nombre de la categoría no puede estar vacío.";
                header("Location:/editor/index");
                exit();
            }

            try {
                $state = $this->categoryDao->crearCategoria($nombre);

                $state == true ? $_SESSION["message"] = "Categoría creada correctamente." : $_SESSION['message'] = "No se pudo crear la categoría.";

            } catch (Exception $e) {
                $_SESSION["message"] = "Error al crear la categoría.";
                header("Location:/editor/index");
                exit();
            }
            header("Location:/editor/index");
            exit();
        }
    }
    public function aprobar()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $idPreguntas = $_POST['ids'] ?? null;

            if (empty($idPreguntas)) {
                $_SESSION['message'] = 'No se seleccionaron preguntas para aprobar.';
                header("Location:/editor/index");
                exit();
            }

            try {
                $state = $this->preguntasDao->aprobarPregunta($idPreguntas);

                $state == true ? $_SESSION["message"] = "Pregunta aprobada correctamente." : $_SESSION['message'] = "No se pudo aprobar la pregunta.";

            } catch (Exception $e) {
                $_SESSION["message"] = "Error al aprobar la pregunta." . $e->getMessage();
                header("Location:/editor/index");
                exit();
            }
            header("Location:/editor/index");
            exit();
        }
    }

    public function eliminarPreguntas()
    {

        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location:/editor/index");
            exit();
        }

        $idPreguntas = $_POST['ids'] ?? [];

        if (empty($idPreguntas)) {
            $_SESSION['message'] = "No se seleccionaron preguntas para eliminar.";
            header("Location:/editor/index");
            exit();
        }

        try {
            $state = $this->preguntasDao->eliminarPreguntasPorIds($idPreguntas);

            if ($state) {
                if (count($idPreguntas) > 1) {
                    $_SESSION["message"] = "Preguntas eliminadas correctamente.";
                } else {
                    $_SESSION["message"] = "Pregunta eliminada correctamente.";
                }
            } else {
                $_SESSION["message"] = "No se pudieron eliminar las preguntas.";
            }

            header("Location:/editor/index");
            exit();

        } catch (Exception $e) {
            $_SESSION['message'] = "Error al eliminar las preguntas." . $e->getMessage();
            header("Location:/editor/index");
            exit();
        }

    }

    public function modificar()
    {

        // $errors = [];
        // $inputs = [];

        // if ($_SERVER["REQUEST_METHOD"] != "GET") {
        //     header("Location:/editor/index");
        //     exit();
        // }

        // $inputs = [
        //     'pregunta_id' => filter_input(INPUT_GET, 'pregunta_id', FILTER_SANITIZE_NUMBER_INT, FILTER_VALIDATE_INT),
        //     'texto' => trim($_POST['texto'] ?? ''),
        //     'genero_id' => filter_input(INPUT_POST, 'genero_id', FILTER_SANITIZE_NUMBER_INT, FILTER_VALIDATE_INT),

        //     'id_correcta' => filter_input(INPUT_POST, 'id_correcta', FILTER_SANITIZE_NUMBER_INT, FILTER_VALIDATE_INT),
        //     'respuestas' => $_POST['respuestas'] ?? []
        // ];

        // if (!$inputs['pregunta_id'])
        //     $errors[] = "ID de pregunta inválido.";
        // if ($inputs['texto'] === '')
        //     $errors[] = "El texto de la pregunta no puede estar vacío.";
        // if (!$inputs['genero_id'])
        //     $errors[] = "Categoría inválida.";
        // if (!$inputs['id_correcta'])
        //     $errors[] = "Debe seleccionarse una respuesta correcta.";
        // if (empty($inputs['respuestas']) || count($inputs['respuestas']) < 4)
        //     $errors[] = "Todas las respuestas deben ser proporcionadas.";

        // foreach ($inputs['respuestas'] as $respuesta) {
        //     if (empty(trim($respuesta))) {
        //         $errors[] = "Las respuestas no pueden estar vacías.";
        //         break;
        //     }
        // }

        // if (!empty($errors)) {
        //     $_SESSION['message'] = implode(' ', $errors);
        //     header("Location:/editor/index");
        //     exit();
        // }

        // try {

        //     $errors[] = $this->preguntasDao->actualizarPregunta($inputs);

        //     empty($errors) ? $_SESSION['message'] = "Pregunta modificada correctamente." : $_SESSION['message'] = implode(' ', $errors);

        //     header("Location:/editor/index");
        //     exit();

        // } catch (Exception $e) {
        //     $_SESSION['message'] = "Error al modificar la pregunta." . $e->getMessage();
        //     header("Location:/editor/index");
        //     exit();
        // }

    }

    public function procesarSugerida()
    {

        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location:/editor/index");
            exit();
        }

        $accion = $_POST['type'] ?? null;

        switch ($accion) {
            case 'aprobar':
                $this->aprobar();
                break;
            case 'eliminar':
            case 'rechazar':
                $this->eliminarPreguntas();
                break;
            default:
                header("Location:/editor/index");
                exit();
        }
    }

    public function procesarReporte()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('location:/editor/index');
            exit();
        }

        $accion = filter_input(INPUT_POST, "accion", FILTER_SANITIZE_STRING);

        if ($accion === null || $accion === false || trim($accion) === "") {
            $_SESSION["message"] = "La accion es incorrecta";
            header("location:/editor/index");
            exit();
        }

        $idPregunta = filter_input(INPUT_POST, "pregunta_id", FILTER_VALIDATE_INT);

        if (!$idPregunta) {
            $_SESSION["message"] = "El id de la pregunta es invalido";
            header('location:/editor/index');
            exit();
        }

        switch ($accion) {
            case 'aprobar';
                $state = $this->reporteDao->aprobarReporte($idPregunta);
                $type = 'aprobado';
                break;
            case 'rechazar':
                $state = $this->reporteDao->rechazarReporte($idPregunta);
                $type = 'rechazado';
                break;
        }

        $state === true ? $_SESSION['message'] = 'Reporte ' . $type . ' con exito.' : $_SESSION['message'] = 'Hubo un error al aprobar el reporte';
        header('location:/editor/index');
        exit();
    }

    public function crearPregunta()
    {
        $this->mustacheRenderer->render("preguntas", [
            "isLogged" => IsLogged::isLogged(),
            "usuario" => $_SESSION['user'],
            "categories" => $this->categoryDao->getAll()
        ]);
    }
}