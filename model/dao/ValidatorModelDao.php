<?php
class ValidatorModelDao
{

    private $usuarioDao;

    public function __construct($usuarioDao)
    {
        $this->usuarioDao = $usuarioDao;
    }

    public function validateUser($usuario, $token)
    {

        if (empty($usuario) || empty($token)) {
            return "Usuario o token inválido.";
        }
        $user = $this->usuarioDao->getUserByUsername($usuario);

        if (!$user) {
            return "Usuario no encontrado.";
        }
        if ($user[0]['token_verificacion'] !== $token) {
            return "Token de verificación inválido.";
        }
        if ($user[0]['activo'] == 1) {
            return "La cuenta ya ha sido verificada.";
        }
        try {
            $this->usuarioDao->activateUser($usuario);
            return "Cuenta verificada exitosamente. Ahora puedes iniciar sesión.";
        } catch (Exception $e) {
            return "Error al verificar la cuenta: " . $e->getMessage();
        }
    }
}
