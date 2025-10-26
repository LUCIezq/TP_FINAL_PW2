<?php
include_once './helper/ValidatorForm.php';
include_once './helper/FileUploader.php';

class RegisterModelDao
{

    private MyConexion $conexion;
    private UsuarioDao $usuarioDao;

    public function __construct(MyConexion $conexion, UsuarioDao $usuarioDao)
    {
        $this->conexion = $conexion;
        $this->usuarioDao = $usuarioDao;
    }

    public function userRegister($inputs)
    {
        $error = [];

        foreach ($inputs as $key => $value) {
            if (ValidatorForm::isFieldEmpty($value)) {
                $error[] = "El campo $key es obligatorio.";
            }

            if ($key === 'email' && !ValidatorForm::isEmailValid($value)) {
                $error[] = "El email no es válido.";
            }
            if ($key === 'password' && !ValidatorForm::isPasswordValid($value, 8)) {
                $error[] = "La contraseña debe tener al menos 8 caracteres.";
            }
            if ($key === 'confirm_password' && !ValidatorForm::doPasswordsMatch($inputs['password'], $value)) {
                $error[] = "Las contraseñas no coinciden.";
            }
        }

        $existingUser = $this->usuarioDao->getUserByUsernameOrEmail($inputs['usuario'], $inputs['email']);

        if ($existingUser) {
            $error[] = "El nombre de usuario o email ya está en uso.";
            return $error;
        }

        $uploadedFilePath = FileUploader::uploadFile('foto', $inputs['usuario']);

        if ($uploadedFilePath === null) {
            $uploadedFilePath = FileUploader::$defaultImgPath;
        }

        $hashedPassword = HashGenerator::generateHash($inputs['password']);

        $token = bin2hex(random_bytes(16));
        $tokenExpiracion = date('Y-m-d H:i:s', strtotime('+1 day'));

        $params = [
            'nombre' => $inputs['nombre'],
            'apellido' => $inputs['apellido'],
            'fecha_nacimiento' => $inputs['fecha'],
            'email' => $inputs['email'],
            'contrasena' => $hashedPassword,
            'nombre_usuario' => $inputs['usuario'],
            'foto_perfil' => $uploadedFilePath,
            'token_verificacion' => $token,
            'token_expiracion' => $tokenExpiracion,
            'sexo_id' => (int)$inputs['gender']
        ];

        try {
            $this->usuarioDao->createUser($params);
        } catch (Exception $e) {
            $errors[] = "Error al registrar el usuario. Por favor, intenta nuevamente. ->" . $e->getMessage();
        }
        return $errors;
    }
}
