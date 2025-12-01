<?php

class PreguntasController
{

    private MustacheRenderer $mustacheRenderer;
    private CategoryDao $categoryDao;
    private PreguntasDao $preguntasDao;


    public function __construct(MustacheRenderer $mustacheRenderer, CategoryDao $categoryDao, PreguntasDao $preguntasDao)
    {
        $this->mustacheRenderer = $mustacheRenderer;
        $this->categoryDao = $categoryDao;
        $this->preguntasDao = $preguntasDao;
    }
    public function index()
    {

        $message = $_SESSION["message"];

        unset($_SESSION["message"]);


        if (!IsLogged::isLogged()) {
            header('location: /login/index');
            exit();
        }

        $categories = $this->categoryDao->getAll();

        $this->mustacheRenderer->render("preguntas", ["categories" => $categories, "message" => $message]);
    }

    public function createQuestion()
    {

        if (!IsLogged::isLogged() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /login/index');
            exit();
        }

        $data = [
            "pregunta" => trim($_POST["pregunta"]),
            "categoriaId" => filter_var($_POST["categoria"], FILTER_VALIDATE_INT),
            "indiceCorrecta" => filter_input(INPUT_POST, "correcta", FILTER_VALIDATE_INT),
            "usuarioId" => $_SESSION["user"]["id"],
            'respuestas' => $_POST['respuestas'] ?? []
        ];


        $errors = [];

        if (!$data["pregunta"]) {
            $errors[] = "El enunciado de la pregunta es obligatorio.";
        }
        if (!$data["categoriaId"]) {
            $errors[] = "La categoría seleccionada no es válida.";
        }
        if ($data['indiceCorrecta'] < 0 || $data['indiceCorrecta'] > 4) {
            $errors[] = "La respuesta correcta no es valida.";
        }
        if ($data['respuestas'] === [] || count($data['respuestas']) < 2) {
            $errors[] = "Se deben proporcionar al menos dos respuestas.";
        } else {
            foreach ($data['respuestas'] as $respuesta) {
                if (trim($respuesta) === '') {
                    $errors[] = "Todas las respuestas deben tener texto.";
                    break;
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION["message"] = implode(" ", $errors);
            header('location: /preguntas/index');
            exit();
        }

        try {

            $result = $this->preguntasDao->createQuestion($data);

            ShowData::show($result);

            $result == true ? $_SESSION["message"] = "Pregunta creada exitosamente. Ya se encuentra en revision" : $_SESSION["message"] = "Error al crear la pregunta.";
            $rol = $_SESSION["user"]['rol_id'];

            if ((int) $rol === UserRole::EDITOR) {
                header('location: /editor/index');
                exit();
            } else {
                header('location: /preguntas/index');
                exit();
            }

        } catch (Exception $e) {
            $errors[] = "Error inesperado: " . $e->getMessage();
        }
    }
}