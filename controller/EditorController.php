<?php
class EditorController
{

    private EditorDao $dao;
    private MustacheRenderer $mustacheRenderer;
    private PreguntasDao $preguntasDao;
    private CategoryDao $categoryDao;

    public function __construct(EditorDao $dao, MustacheRenderer $mustacheRenderer, PreguntasDao $preguntasDao, CategoryDao $categoryDao)
    {
        $this->dao = $dao;
        $this->mustacheRenderer = $mustacheRenderer;
        $this->preguntasDao = $preguntasDao;
        $this->categoryDao = $categoryDao;
    }

    public function index()
    {
        $message = $_SESSION["message"] ?? null;
        unset($_SESSION["message"]);

        if (!IsLogged::isLogged()) {
            header("Location:/login/index");
            exit();
        }

        $role = $_SESSION['user']['rol_id'] ?? null;

        if ($role != UserRole::EDITOR) {
            header("Location:/home/index");
            exit();
        }

        $filters = [
            'type' => htmlspecialchars(trim($_GET["type"] ?? "sistema"), ENT_QUOTES, 'UTF-8'),
            'category_id' => htmlspecialchars(trim($_GET["category_id"] ?? ""), ENT_QUOTES, 'UTF-8')
        ];

        $questions = $this->preguntasDao->getQuestionsWithFilter($filters);
        ;
        $categories = $this->categoryDao->getAll();

        foreach ($categories as &$category) {
            $category['checked'] = $filters['category_id'] === $category['id'] ? 'checked' : '';
        }

        foreach ($questions as &$p) {
            $p['categorias'] = [];

            foreach ($categories as $c) {
                $p['categorias'][] = [
                    'id' => $c['id'],
                    'nombre' => $c['nombre'],
                    'selected' => ($c['id'] == $p['genero_id']) ? 'selected' : ''
                ];
            }
        }
        unset($p);

        $this->mustacheRenderer->render("editor", [
            "questions" => $questions,
            "isLogged" => IsLogged::isLogged(),
            "categories" => $categories,
            "message" => $message,
            "usuario" => $_SESSION['user'],
            "isSistema" => $filters['type'] == "sistema",
            "isSugeridas" => $filters['type'] == "sugeridas",
        ]);
    }

    public function aprobar()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $pregunta = $this->preguntasDao->getQuestionById($_POST["pregunta_id"]);

            if (!$pregunta) {
                header("Location:/editor/index");
                exit();
            }

            $data = [
                "id" => htmlspecialchars(trim($_POST["pregunta_id"]), ENT_QUOTES, 'UTF-8'),
                "texto" => htmlspecialchars(trim($_POST["texto"]), ENT_QUOTES, 'UTF-8'),
                "genero_id" => htmlspecialchars(trim($_POST["genero_id"]), ENT_QUOTES, 'UTF-8'),
                "opcion_correcta" => htmlspecialchars(trim($_POST["id_correcta"]), ENT_QUOTES, 'UTF-8')
            ];

            foreach ($data as $row) {
                if (empty($row)) {
                    $_SESSION["message"] = "Todos los campos son obligatorios.";
                    header("Location:/editor/index");
                    exit();
                }
            }

            try {
                $state = $this->preguntasDao->aprobarPregunta($pregunta['pregunta_id']);

                $state == true ? $_SESSION["message"] = "Pregunta aprobada correctamente." : $_SESSION['message'] = "No se pudo aprobar la pregunta.";

            } catch (Exception $e) {
                $_SESSION["message"] = "Error al aprobar la pregunta.";
                header("Location:/editor/index");
                exit();
            }
            header("Location:/editor/index");
            exit();
        }
    }

    public function rechazar()
    {

        $pregunta = $this->preguntasDao->getQuestionById($_POST["pregunta_id"]);

        if (!$pregunta) {
            header("Location:/editor/index");
            exit();
        }

        try {
            $state = $this->preguntasDao->rechazarPregunta($_POST["pregunta_id"]);

            $state == true ? $_SESSION["message"] = "Pregunta rechazada correctamente." : $_SESSION['message'] = "No se pudo rechazar la pregunta.";

        } catch (Exception $e) {
            $_SESSION["message"] = "Error al rechazar la pregunta.";
            header("Location:/editor/index");
            exit();
        }
        header("Location:/editor/index");
        exit();

    }

    public function procesarPregunta()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $accion = $_POST['accion'] ?? null;

            if ($accion === 'aprobar') {
                $this->aprobar();
            } elseif ($accion === 'rechazar') {
                $this->rechazar();
            } else {
                header("Location:/editor/index");
                exit();
            }
        }
    }

}