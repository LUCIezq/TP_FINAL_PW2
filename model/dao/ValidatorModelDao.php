<?php
class ValidatorModelDao
{

    private UsuarioDao $usuarioDao;

    public function __construct(UsuarioDao $usuarioDao)
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

        if (strtotime($user[0]['token_expiracion']) < time()) {

            $token = $this->generateNewToken($user[0]);

            $url = "http://localhost/validator/validate";
            SendValidationEmail::sendValidationEmail($user[0]['email'], $user[0]['nombre_usuario'], $token, $url);

            return "El token ha expirado. Se ha enviado un nuevo correo de verificación.";
        }

        try {
            $this->usuarioDao->activateUser($usuario);
        } catch (Exception $e) {
            return "Error al verificar la cuenta: " . $e->getMessage();
        }
    }

    public function generateNewToken($user)
    {
        $token = bin2hex(random_bytes(16));
        $tokenExpiracion = date('Y-m-d H:i:s', strtotime('+1 day'));

        $this->usuarioDao->updateUserToken($user['nombre_usuario'], $token, $tokenExpiracion);

        return $user['token_verificacion'];
    }
}
