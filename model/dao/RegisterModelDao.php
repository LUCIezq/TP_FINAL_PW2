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

        if (!empty($error)) {
            return $error;
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

        $params = [
            'nombre' => $inputs['nombre'],
            'apellido' => $inputs['apellido'],
            'fecha_nacimiento' => $inputs['fecha'],
            'email' => $inputs['email'],
            'contrasena' => $hashedPassword,
            'nombre_usuario' => $inputs['usuario'],
            'foto_perfil' => $uploadedFilePath,
            'token_verificacion' => $token,
            'sexo_id' => (int)$inputs['gender']
        ];

        try {
            $result = $this->usuarioDao->createUser($params);

            if ($result) {

                $url = "http://localhost/validator/validate";

                SendValidationEmail::sendValidationEmail($inputs['email'], $inputs['usuario'], $token, $url);

                $_SESSION['message'] = "Registro exitoso! Por favor, revisa tu correo para activar tu cuenta.";
            }
        } catch (Exception $e) {
            $errors[] = "Error al registrar el usuario. Por favor, intenta nuevamente.";
        }
        return $errors;
    }
}
