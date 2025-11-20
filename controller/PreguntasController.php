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

        $success_message = $_SESSION["success_message"];
        $error_message = $_SESSION["error_message"];

        unset($_SESSION["success_message"]);
        unset($_SESSION["error_message"]);


        if (!IsLogged::isLogged()) {
            header('location: /login/index');
            exit();
        }

        $categories = $this->categoryDao->getAll();

        $this->mustacheRenderer->render("preguntas", ["categories" => $categories, "success_message" => $success_message, "error_message" => $error_message]);

    }

    public function createQuestion()
    {

        if (!IsLogged::isLogged() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('location: /login/index');
            exit();
        }

        $data = [
            "pregunta" => trim($_POST["pregunta"]),
            "categoriaId" => filter_var($_POST["categoria"], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]),
            "indiceCorrecta" => filter_var($_POST["correcta"], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1,]]),
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
        if (!$data["indiceCorrecta"]) {
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

        if (empty($errors)) {
            $_SESSION["error_message"] = implode(" ", $errors);
            header('location: /preguntas/index');
            exit();
        }

        try {

            $result = $this->preguntasDao->createQuestion($data);

            if ($result["created"] !== true) {
                $_SESSION["error_message"] = "Error al crear la pregunta.";
                header('location: /preguntas/index');
                exit();
            }

        } catch (Exception $e) {
            $errors[] = "Error inesperado: " . $e->getMessage();
        }
    }
}