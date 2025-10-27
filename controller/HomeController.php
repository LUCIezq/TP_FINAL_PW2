<?php

include_once 'helper/IsLogged.php';

class HomeController
{
    private MustacheRenderer $mustacheRenderer;

    public function __construct(MustacheRenderer $mustacheRenderer)
    {
        $this->mustacheRenderer = $mustacheRenderer;
    }

    public function index()
    {
        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit;
        }

        $this->mustacheRenderer->render(
            "home",

            [
                "usuario" => $_SESSION['user']['nombre_usuario'],
                "url_profile" => $_SESSION['user']['foto_perfil']
            ]
        );
    }
}