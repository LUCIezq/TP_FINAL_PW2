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
            "categories" => $categories
        ]);
    }

    public function aprobar()
    {

        ShowData::show($_POST);
    }


}