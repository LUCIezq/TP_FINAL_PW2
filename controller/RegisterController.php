<?php

include_once './helper/ShowData.php';
include_once './helper/ValidatorForm.php';
include_once './helper/FileUploader.php';
include_once './helper/HashGenerator.php';
include_once './helper/SendValidationEmail.php';

class RegisterController
{
    private GeneroDao $generoDao;
    private UsuarioDao $usuarioDao;
    private MustacheRenderer $renderer;

    public function __construct(GeneroDao $generoDao, UsuarioDao $usuarioDao, MustacheRenderer $renderer)
    {
        $this->generoDao = $generoDao;
        $this->usuarioDao = $usuarioDao;
        $this->renderer = $renderer;
    }

    public function index($errors = [])
    {

        $data = $this->getDataFormRegister();

        $this->renderer->render("register", [
            "data" => $data,
            "genders" => $this->generoDao->getAllGenders(),
            "errors" => $errors
        ]);
    }

    public function getDataFormRegister()
    {
        $formData = [
            'titulo' => 'Registro de usuario',
            'campos' => [
                ['name' => 'nombre', 'type' => 'text', 'placeholder' => '@alguien', 'required' => true, 'id' => 'nombre'],
                ['name' => 'apellido', 'type' => 'text', 'placeholder' => '@alguien', 'required' => true, 'id' => 'apellido'],
                ['name' => 'fecha', 'type' => 'date', 'required' => true, 'id' => 'fecha'],
                ['name' => 'usuario', 'type' => 'text', 'placeholder' => '@alguien', 'required' => true, 'id' => 'usuario'],
                ['name' => 'email', 'type' => 'email', 'placeholder' => 'alguien@alguien.com', 'id' => 'email', 'required' => true],
                ['name' => 'password', 'type' => 'password', 'placeholder' => '**********', 'required' => true, 'id' => 'password'],
                ['name' => 'confirm_password', 'type' => 'password', 'placeholder' => '**********', 'required' => true, 'id' => 'confirm_password'],
                ['name' => 'foto', 'type' => 'file', 'id' => 'foto'],
            ]
        ];
        return $formData;
    }

    public function userRegister()
    {
        $errors = [];

        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $genero_id = $_POST['gender'] ?? '';
        $pais = $_POST['pais'] ?? '';
        $ciudad = $_POST['ciudad'] ?? '';

        if (ValidatorForm::isFieldEmpty($nombre)) $errors[] = "El nombre es obligatorio.";
        if (ValidatorForm::isFieldEmpty($apellido)) $errors[] = "El apellido es obligatorio.";
        if (ValidatorForm::isFieldEmpty($fecha)) $errors[] = "El año de nacimiento es obligatorio.";
        if (ValidatorForm::isFieldEmpty($usuario)) $errors[] = "El nombre de usuario es obligatorio.";
        if (!ValidatorForm::isEmailValid($email)) $errors[] = "El email no es válido.";
        if (!ValidatorForm::isPasswordValid($password, 8)) $errors[] = "La contraseña debe tener al menos 8 caracteres.";
        if (!ValidatorForm::doPasswordsMatch($password, $confirm_password)) $errors[] = "Las contraseñas no coinciden.";
        if (ValidatorForm::isFieldEmpty($genero_id)) $errors[] = "El género es obligatorio.";

        if (empty($errors)) {

            $existingUser = $this->usuarioDao->getUserByUsernameOrEmail($usuario, $email);

            if (!empty($existingUser)) {
                foreach ($existingUser as $user) {
                    if ($user['nombre_usuario'] === $usuario) {
                        $errors[] = "El nombre de usuario ya está en uso.";
                    }
                    if ($user['email'] === $email) {
                        $errors[] = "El email ya está en uso.";
                    }
                }
                $this->index($errors);
                return;
            }

            $defaultImgPath = 'uploads/profiles/default.png';

            $uploadedFilePath = FileUploader::uploadFile('foto', $usuario);

            if ($uploadedFilePath === null) {
                $uploadedFilePath = $defaultImgPath;
            }

            $hashedPassword = HashGenerator::generateHash($password);
            $token = bin2hex(random_bytes(16));

            $params = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'fecha_nacimiento' => $fecha,
                'email' => $email,
                'contrasena' => $hashedPassword,
                'nombre_usuario' => $usuario,
                'foto_perfil' => $uploadedFilePath,
                'token_verificacion' => $token,
                'sexo_id' => (int)$genero_id
            ];


            try {
                $result = $this->usuarioDao->createUser($params);

                if ($result) {

                    $url = "http://localhost/validator/validate";

                    SendValidationEmail::sendValidationEmail($email, $usuario, $token, $url);

                    $_SESSION['message'] = "Registro exitoso! Por favor, revisa tu correo para activar tu cuenta.";
                    header("Location: /login/index");
                    exit();
                }
            } catch (Exception $e) {
                $errors[] = "Error al registrar el usuario. Por favor, intenta nuevamente.";
            }
        }

        $this->index($errors);
        return;
    }
}
