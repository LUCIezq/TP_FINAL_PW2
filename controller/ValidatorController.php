<?php

class ValidatorController
{

    private $validatorModelDao;

    public function __construct($validatorModelDao)
    {
        $this->validatorModelDao = $validatorModelDao;
    }

    public function validate()
    {
        $usuario = $_GET['usuario'] ?? '';
        $token = $_GET['token'] ?? '';

        $_SESSION['message'] = $this->validatorModelDao->validateUser($usuario, $token);

        header("Location: /login/index");
        exit();
    }
}
