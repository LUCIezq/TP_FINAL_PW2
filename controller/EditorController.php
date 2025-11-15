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
            'category_id' => htmlspecialchars(trim($_GET["category_id"] ?? ""), ENT_QUOTES, 'UTF-8'),
            'eliminar_categoria_id' => filter_input(
                INPUT_GET,
                'eliminar_categoria_id',
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 1
                    ]
                ]
            )
        ];

        if ($filters['eliminar_categoria_id']) {
            $_SESSION["message"] = $this->eliminarCategoria($filters['eliminar_categoria_id']);
            header("Location:/editor/index");
            exit();
        }

        $questions = $this->preguntasDao->getQuestionsWithFilter($filters);
        ;
        $categories = $this->categoryDao->getAll();

        foreach ($categories as &$category) {
            $category['checked'] = $filters['category_id'] === $category['id'] ? 'checked' : '';
            $category['esIdValido'] = $category['id'] != '' ? true : false;
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

        array_unshift($categories, ['id' => '', 'nombre' => 'Todas', 'checked' => $filters['category_id'] === '' ? 'checked' : '']);

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

    private function eliminarCategoria($idCategoria)
    {
        $preguntas = $this->preguntasDao->getPreguntasPorCategoria($idCategoria);

        if (count($preguntas) > 0) {
            return "No se puede eliminar la categoría porque tiene preguntas asociadas.";
        }

        return $this->categoryDao->eliminarCategoria($idCategoria);
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

    public function modificar()
    {

        // 1 - Recibir datos por POST
        // 2 - Validar y sanitizar datos
        // 3 - Consultar en la bd si existe la pregunta
        // 4 - Si existe validar campo a campo para actualizar unicamente el que se modifico.
        // 5 - Guardamos en un array todos los campos actualizados.
        // 6 - LLamar al metodo del dao para actualizar la pregunta.
        // 7 - Redirigir con mensaje de exito o error.

        $errors = [];
        $inputs = [];

        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location:/editor/index");
            exit();
        }

        $inputs = [
            'pregunta_id' => filter_input(INPUT_POST, 'pregunta_id', FILTER_SANITIZE_NUMBER_INT, FILTER_VALIDATE_INT),
            'texto' => trim($_POST['texto'] ?? ''),
            'genero_id' => filter_input(INPUT_POST, 'genero_id', FILTER_SANITIZE_NUMBER_INT, FILTER_VALIDATE_INT),
            'id_correcta' => filter_input(INPUT_POST, 'id_correcta', FILTER_SANITIZE_NUMBER_INT, FILTER_VALIDATE_INT),
            'respuestas' => $_POST['respuestas'] ?? []
        ];

        if (!$inputs['pregunta_id'])
            $errors[] = "ID de pregunta inválido.";
        if ($inputs['texto'] === '')
            $errors[] = "El texto de la pregunta no puede estar vacío.";
        if (!$inputs['genero_id'])
            $errors[] = "Categoría inválida.";
        if (!$inputs['id_correcta'])
            $errors[] = "Debe seleccionarse una respuesta correcta.";
        if (empty($inputs['respuestas']) || count($inputs['respuestas']) < 4)
            $errors[] = "Todas las respuestas deben ser proporcionadas.";

        foreach ($inputs['respuestas'] as $respuesta) {
            if (empty(trim($respuesta))) {
                $errors[] = "Las respuestas no pueden estar vacías.";
                break;
            }
        }

        if (!empty($errors)) {
            $_SESSION['message'] = implode(' ', $errors);
            header("Location:/editor/index");
            exit();
        }

        try {

            $errors[] = $this->preguntasDao->actualizarPregunta($inputs);

            empty($errors) ? $_SESSION['message'] = "Pregunta modificada correctamente." : $_SESSION['message'] = implode(' ', $errors);

            header("Location:/editor/index");
            exit();

        } catch (Exception $e) {
            $_SESSION['message'] = "Error al modificar la pregunta.";
            header("Location:/editor/index");
            exit();
        }
    }

    public function procesarPregunta()
    {

        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location:/editor/index");
            exit();
        }
        $accion = $_POST['accion'] ?? null;

        if ($accion === 'aprobar') {
            $this->aprobar();
        } elseif ($accion === 'rechazar') {
            $this->rechazar();
        } elseif ($accion === 'modificar') {
            $this->modificar();
        } else {
            header("Location:/editor/index");
            exit();
        }
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