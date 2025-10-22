<?php
class RegisterController
{
    private GeneroDao $generoDao;
    private MustacheRenderer $renderer;

    public function __construct(GeneroDao $generoDao, MustacheRenderer $renderer)
    {
        $this->generoDao = $generoDao;
        $this->renderer = $renderer;
    }

    public function index()
    {
        $data = $this->getDataFormRegister();
        $this->renderer->render("register", [
            "data" => $data,
            "genders" => $this->generoDao->getAllGenders()
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
                ['name' => 'email', 'type' => 'email', 'placeholder' => '', 'id' => 'email'],
                ['name' => 'password', 'type' => 'password', 'placeholder' => '**********', 'required' => true, 'id' => 'password'],
                ['name' => 'confirm_password', 'type' => 'password', 'placeholder' => '**********', 'required' => true, 'id' => 'confirm_password'],
                ['name' => 'foto', 'type' => 'file', 'id' => 'foto'],

            ]
        ];
        return $formData;
    }
}
