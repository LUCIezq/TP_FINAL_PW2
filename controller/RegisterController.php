<?php

include_once './helper/ShowData.php';
include_once './helper/ValidatorForm.php';

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
                ['name' => 'anio_nacimiento', 'type' => 'date', 'required' => true, 'id' => 'anio_nacimiento'],
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
        ShowData::show($_POST);

        $errors = [];

        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $anio_nacimiento = $_POST['anio_nacimiento'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $photo = $_FILES['foto'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $genero_id = $_POST['gender'] ?? '';
        $pais = $_POST['pais'] ?? '';
        $ciudad = $_POST['ciudad'] ?? '';


        if (ValidatorForm::isFieldEmpty($nombre)) $errors[] = "El nombre es obligatorio.";

        if (ValidatorForm::isFieldEmpty($apellido)) $errors[] = "El apellido es obligatorio.";

        if (ValidatorForm::isFieldEmpty($anio_nacimiento)) $errors[] = "El año de nacimiento es obligatorio.";

        if (ValidatorForm::isFieldEmpty($usuario)) {
            $errors[] = "El nombre de usuario es obligatorio.";
        } else {
            $existingUser = $this->usuarioDao->findByUsername($usuario);
            if (!ValidatorForm::isNull($existingUser[0])) {
                $errors[] = "El nombre de usuario ya está en uso.";
            }
        }

        if (!ValidatorForm::isEmailValid($email)) {
            $errors[] = "El email no es válido.";
        } else {
            $existingUser = $this->usuarioDao->findByEmail($email);
            if (!ValidatorForm::isNull($existingUser[0])) {
                $errors[] = "El email ya está registrado.";
            }
        }

        if (!ValidatorForm::isPasswordValid($password, 8)) {
            $errors[] = "La contraseña debe tener al menos 8 caracteres.";
        } else {
            if (!ValidatorForm::doPasswordsMatch($password, $confirm_password)) {
                $errors[] = "Las contraseñas no coinciden.";
            }
        }

        if (ValidatorForm::isFieldEmpty($genero_id)) $errors[] = "El género es obligatorio.";

        if (ValidatorForm::isFieldEmpty($pais)) $errors[] = "El país es obligatorio.";

        if (ValidatorForm::isFieldEmpty($ciudad)) $errors[] = "La ciudad es obligatoria.";

        
    }
}
