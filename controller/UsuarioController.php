<?php

class UsuarioController
{
    private UsuarioDao $usuarioDao;
    private MustacheRenderer $mustacheRenderer;

    public function __construct(
        UsuarioDao $usuarioDao,
        MustacheRenderer $mustacheRenderer
    ) {
        $this->usuarioDao = $usuarioDao;
        $this->mustacheRenderer = $mustacheRenderer;
    }

    public function perfil()
    {

        $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($id === null || $id === false) {
            header('location: /home/index');
            exit();
        }

        $id = (int) $id;
        $isOwner = IsLogged::isLogged() && isset($_SESSION['user']) && $_SESSION['user']['id'] === $id;

        $usuario = $this->usuarioDao->findById($id);

        if (empty($usuario)) {
            header('location: /home/index');
            exit();
        }
        $qr = null;

        $gameDao = new GameDao($this->usuarioDao->getConnection());
        $estadisticas = $gameDao->obtenerEstadisticasUsuario($id);
        $usuario = array_merge($usuario, $estadisticas);

        try {
            if (!class_exists('QrGenerator')) {
                throw new Exception('La clase QrGenerator no está disponible.');
            }
            $qr = QrGenerator::generateQr($usuario['id']);

        } catch (Exception $e) {
            throw new Exception('' . $e->getMessage());
        }
        $this->mustacheRenderer->render(
            "perfilUsuario",
            [
                "usuario" => $usuario,
                "qr" => $qr,
                "isOwner" => $isOwner,

            ]
        );

    }

    public function actualizarPerfil()
    {
        if (!IsLogged::isLogged()) {
            header("location:/login/index");
            exit();
        }

        $data = [
            'id' => filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT),
            'nombre' => filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING),
            'apellido' => filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING),
            'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
            'nombre_usuario' => filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING),
            'imagen' => $_FILES['imagen'] ?? null,
        ];

        $errors = [];

        if ($data['id'] == false) {
            $errors[] = 'Id invalido';
        }
        if (empty(trim($data['nombre'])) || empty(trim($data['nombre'])) || empty(trim($data['apellido'])) || empty(trim($data['email'])) || empty(trim($data['nombre_usuario']))) {
            $errors[] = 'Todos los campos son obligatorios.';
        }

        if (!empty($errors)) {
            $_SESSION['message'] = implode(' ', $errors);
            header('location:/usuario/editar');
            exit();
        }

        try {
            $updated = $this->usuarioDao->actualizarPerfil($data);

            if ($updated) {
                $_SESSION['message'] = 'Perfil actualizado con éxito.';
                $_SESSION['user']['nombre'] = $data['nombre'];
                $_SESSION['user']['apellido'] = $data['apellido'];
                $_SESSION['user']['email'] = $data['email'];
                $_SESSION['user']['nombre_usuario'] = $data['nombre_usuario'];
            } else {
                $_SESSION['message'] = 'No se realizaron cambios en el perfil.';
            }

            header('location:/usuario/editar');
            exit();

        } catch (Exception $e) {
            $_SESSION['message'] = $e->getMessage();
            header('location:/usuario/editar');
            exit();
        }

    }

    public function editar()
    {
        if (!IsLogged::isLogged()) {
            header('location: /login/index');
            exit();
        }

        $message = $_SESSION['message'] ?? null;
        unset($_SESSION['message']);

        $user = $this->usuarioDao->obtenerUsuarioPorId($_SESSION['user']['id']);

        if ($user === null) {
            header('location:/usuario/perfil');
            exit();
        }

        $this->mustacheRenderer->render(
            "perfil",
            [
                "usuario" => $user
                ,
                "message" => $message
            ],
        );
    }

    public function getCountryAndCity()
    {

        if (!IsLogged::isLogged()) {
            header('location: /login/index');
            exit();
        }

        $id = $_SESSION['user']['id'] ?? null;

        if (empty($id) || !is_numeric($id)) {
            header('location: /home/index');
            exit();
        }

        $data = $this->usuarioDao->getCountryAndCityById($id);

        if (empty($data)) {
            header('location: /home/index');
            exit();
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}