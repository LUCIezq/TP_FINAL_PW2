<?php

include_once './helper/HashGenerator.php';

class LoginController
{
    private MustacheRenderer $renderer;
    private LoginModelDao $loginModel;


    public function __construct(MustacheRenderer $renderer, LoginModelDao $loginModel)
    {
        $this->renderer = $renderer;
        $this->loginModel = $loginModel;
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
            header("Location: /home/index");
            exit();
        }
    }
    public function logout()
    {
        $_SESSION['logged_in'] = false;
        header("Location: /login/index");
        exit();
    }
}
