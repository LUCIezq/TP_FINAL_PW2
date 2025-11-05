<?php



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
            $user = $this->usuarioDao->findByEmail($email);

            if (!$user || empty($user)) {
                return ["Usuario/contraseña inválidos."];
            }

            if (!HashGenerator::verifyHash($password, $user['contrasena'])) {
                return ["Usuario/contraseña inválidos."];
            }

            if ($user['token_verificacion'] !== null) {
                return ["Debes activar tu cuenta antes de iniciar sesión. Revisa tu correo electrónico."];
            }
            return [];
        } catch (Exception $e) {
            return ["Error interno. Por favor, intenta nuevamente."];
        }
    }
}
