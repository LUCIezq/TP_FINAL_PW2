<?php

class ValidatorController
{

    private $renderer;
    private $usuarioDao;

    public function __construct(MustacheRenderer $renderer, UsuarioDao $usuarioDao)
    {
        $this->renderer = $renderer;
        $this->usuarioDao = $usuarioDao;
    }

    public function validate()
    {
        $usuario = $_GET['usuario'] ?? '';
        $token = $_GET['token'] ?? '';

        if (empty($usuario) || empty($token)) {
            $_SESSION['message'] = "Enlace de validación inválido.";
        }

        $user = $this->usuarioDao->getUserByUsername($usuario);

        if ($user[0] && $user[0]['token_verificacion'] === $token) {
            $this->usuarioDao->activateUser($usuario);
            $_SESSION['message'] = "Cuenta activada exitosamente. Ya puedes iniciar sesión.";
        } else {
            $_SESSION['message'] = "Enlace de validación inválido o expirado.";
        }
        header("Location: /login/index");
        exit();
    }
}
