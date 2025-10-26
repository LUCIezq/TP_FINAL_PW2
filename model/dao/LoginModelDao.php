<?php

include_once './helper/HashGenerator.php';

class LoginModelDao
{
    private UsuarioDao $usuarioDao;

    public function __construct(UsuarioDao $usuarioDao)
    {
        $this->usuarioDao = $usuarioDao;
    }

    public function login(string $email, string $password)
    {
        if (empty($email) || empty($password)) {
            return ["Todos los campos son obligatorios."];
        }

        try {
            $user = $this->usuarioDao->findByEmail($email)[0];

            if (!$user || empty($user)) {
                return ["Credenciales inválidas."];
            }

            if (!HashGenerator::verifyHash($password, $user['contrasena'])) {
                return ["Credenciales inválidas."];
            }

            if ($user['token_verificacion'] !== null) {
                return ["Debes activar tu cuenta antes de iniciar sesión. Revisa tu correo electrónico."];
            }

            $this->createUserSession($user);
            return [];
        } catch (Exception $e) {
            return ["Error interno. Por favor, intenta nuevamente."];
        }
    }
    private function createUserSession($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['nombre_usuario'];
        $_SESSION['logged_in'] = true;
    }
}
