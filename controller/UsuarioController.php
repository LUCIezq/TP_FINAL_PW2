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

    /*🔋🔋🔋🔋🔋🔋
     * METODO PERFIL() que en mi codigo funcion
     * public function perfil()
    {
        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        $id = $_GET["id"] ?? null;

        if (empty($id) || !is_numeric($id)) {
            header('location: /home/index');
            exit();
        }

        $usuario = $this->usuarioDao->findById($id);

        if (empty($usuario)) {
            header('location: /home/index');
            exit();
        }

        // Obtener estadisticas de partidas del usuario
        //getConnection creada en UsuarioDao
        $gameDao = new GameDao($this->usuarioDao->getConnection());

        $estadisticas = $gameDao->obtenerEstadisticasUsuario($id);

        // Combinar datos de usuario con estadísticas
        $usuario = array_merge($usuario, $estadisticas);

        // Renderizar vista con toda la información
        $this->mustacheRenderer->render(
            "perfilUsuario",
            [
                "usuario" => $usuario,
                "qr" => QrGenerator::generateQr($usuario['id'])
            ]
        );
    } 🔋🔋🔋🔋🔋🔋
     */

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