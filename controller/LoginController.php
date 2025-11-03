<?php

class LoginController
{
    private MustacheRenderer $renderer;
    private LoginModelDao $loginModel;

    private UsuarioDao $usuarioDao;


    public function __construct(MustacheRenderer $renderer, LoginModelDao $loginModel, UsuarioDao $usuarioDao)
    {
        $this->renderer = $renderer;
        $this->loginModel = $loginModel;
        $this->usuarioDao = $usuarioDao;
    }

    public function index($errors = [])
    {
        $message = $_SESSION['message'] ?? '';
        $errors = $_SESSION['errors'] ?? [];

        unset($_SESSION['errors']);
        unset($_SESSION['message']);

        $this->renderer->render("login", [
            "errors" => $errors,
            "message" => $message,
            "hasMessage" => !empty($message),
        ]);
    }

    public function login()
    {

        $email = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'] ?? '';
        $errors = [];

        $errors = $this->loginModel->login($email, $password);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->index();
        } else {
            CreateUserSession::create($this->usuarioDao->findByEmail($email)[0]);
            header("Location: /home/index");
            exit();
        }
    }
    public function logout()
    {

        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(
                session_name(),
                '',
                time() - 3600,
                '/'
            );
        }
        session_destroy();

        header("Location: /login/index");
        exit();
    }
}
