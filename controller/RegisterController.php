<?php

include_once './helper/ShowData.php';
include_once './helper/HashGenerator.php';
include_once './helper/SendValidationEmail.php';

class RegisterController
{
    private GeneroDao $generoDao;
    private UsuarioDao $usuarioDao;
    private MustacheRenderer $renderer;
    private RegisterModelDao $registerModelDao;

    public function __construct(GeneroDao $generoDao, UsuarioDao $usuarioDao, MustacheRenderer $renderer, RegisterModelDao $registerModelDao)
    {
        $this->generoDao = $generoDao;
        $this->usuarioDao = $usuarioDao;
        $this->renderer = $renderer;
        $this->registerModelDao = $registerModelDao;
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
        $inputs = [];

        $inputs['nombre'] = $_POST['nombre'] ?? '';
        $inputs['apellido'] = $_POST['apellido'] ?? '';
        $inputs['fecha'] = $_POST['fecha'] ?? '';
        $inputs['usuario'] = $_POST['usuario'] ?? '';
        $inputs['email'] = $_POST['email'] ?? '';
        $inputs['password'] = $_POST['password'] ?? '';
        $inputs['confirm_password'] = $_POST['confirm_password'] ?? '';
        $inputs['gender'] = $_POST['gender'] ?? '';
        $inputs['pais'] = $_POST['pais'] ?? '';
        $inputs['ciudad'] = $_POST['ciudad'] ?? '';

        $errors = $this->registerModelDao->userRegister($inputs);

        if (!empty($errors)) {
            $this->index($errors);
        } else {
            $token = $this->getTokenByUsername($inputs['usuario']);

            SendValidationEmail::sendValidationEmail($inputs['email'], $inputs['usuario'], $token);

            $_SESSION['message'] = "Registro exitoso! Por favor, revisa tu correo para activar tu cuenta.";
            header("Location: /login/index");
            exit();
        }
    }
    public function getTokenByUsername($username)
    {
        $user = $this->usuarioDao->getUserByUsername($username);
        return $user ? $user[0]['token_verificacion'] : null;
    }
}
