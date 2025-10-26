<?php

class ValidatorController
{

    private ValidatorModelDao $validatorModelDao;

    public function __construct(ValidatorModelDao $validatorModelDao)
    {
        $this->validatorModelDao = $validatorModelDao;
    }

    public function validate()
    {
        $usuario = $_GET['usuario'] ?? '';
        $token = $_GET['token'] ?? '';
        $error = [];

        $error = $this->validatorModelDao->validateUser($usuario, $token);

        if (!empty($error)) {
            $_SESSION['errors'] = $error;
        } else {
            $_SESSION['message'] = "Cuenta verificada exitosamente. Ahora puedes iniciar sesión.";
        }

        header("Location: /login/index");
        exit();
    }
}
