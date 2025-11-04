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

        $data = [
            "pregunta" => $_POST["pregunta"],
            "categoriaId" => (int) $_POST["categoria"],
            "indiceCorrecta" => (int) $_POST["correcta"]
        ];

        $result = $this->preguntasDao->createQuestion($data);

        if ($result["created"] !== true) {
            $_SESSION["error_message"] = "Error al crear la pregunta.";
            header('location: /preguntas/index');
            exit();
        }

        $id = (int) $result['lastInsertId'];

        for ($i = 1; $i <= 3; $i++) {
            $text = $_POST["respuesta" . $i];
            $isCorrect = ($i == $data["indiceCorrecta"]) ? 1 : 0;

            $this->preguntasDao->createAnswer($text, $isCorrect, $id);
        }

        $_SESSION["success_message"] = "Pregunta creada exitosamente.";
        header('location: /preguntas/index');
        exit();
    }
}