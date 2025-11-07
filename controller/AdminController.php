<?php
class AdminController
{
    private MustacheRenderer $mustacheRenderer;

    public function __construct(MustacheRenderer $mustacheRenderer)
    {
        $this->mustacheRenderer = $mustacheRenderer;

    }
    public function index()
    {

        if (!IsLogged::isLogged()) {
            header("Location:/login/index");
            exit();
        }

        $role = $_SESSION['user']['rol_id'] ?? null;

        if ($role != UserRole::ADMIN) {
            header("Location:/home/index");
            exit();
        }

        $this->mustacheRenderer->render("admin", []);
    }
}