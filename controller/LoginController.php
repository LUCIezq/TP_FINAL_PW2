<?php

class LoginController
{
    private UsuarioDao $usuarioDao;
    private MustacheRenderer $renderer;
    private $config;

    public function __construct(UsuarioDao $usuarioDao, MustacheRenderer $renderer, $config)
    {
        $this->usuarioDao = $usuarioDao;
        $this->renderer = $renderer;
        $this->config = $config;
    }

    public function index($errors = [])
    {
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $this->renderer->render("login", [
            "errors" => $errors,
            "message" => $message,
            "hasMessage" => !empty($message),
        ]);
    }
}
