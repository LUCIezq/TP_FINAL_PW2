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

        $questionsByUsers = $this->preguntasDao->getAllQuestionByUsers();
        $categories = $this->categoryDao->getAll();

        $this->mustacheRenderer->render("editor", [
            "questions" => $questionsByUsers,
            "categories" => $categories,
            "isLogged" => IsLogged::isLogged(),
            "message" => $message
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



}