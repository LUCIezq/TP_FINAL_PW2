<?php

include_once 'helper/HashGenerator.php';
include_once 'helper/StartSession.php';
include_once 'helper/CreateUserSession.php';
include_once 'helper/StartSession.php';
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
        StartSession::start();
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

        CreateUserSession::create($this->usuarioDao->findByEmail($email)[0]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->index();
        } else {

            header("Location: /home/index");
            exit();
        }
    }
    public function logout()
    {

        StartSession::start();
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
