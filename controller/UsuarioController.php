<?php

class UsuarioController
{
    private UsuarioDao $usuarioDao;
    private EstadisticasDao $estadisticasDao;
    private MustacheRenderer $mustacheRenderer;

    public function __construct(
        UsuarioDao $usuarioDao,
        EstadisticasDao $estadisticasDao,
        MustacheRenderer $mustacheRenderer
    ) {
        $this->usuarioDao = $usuarioDao;
        $this->estadisticasDao = $estadisticasDao;
        $this->mustacheRenderer = $mustacheRenderer;
    }

    public function perfil(){
        $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($id === null || $id === false) {
            header('location: /home/index');
            exit();
        }

        $id = (int) $id;

        $isOwner = IsLogged::isLogged() 
            && isset($_SESSION['user']) 
            && $_SESSION['user']['id'] === $id;

        $usuario = $this->usuarioDao->findById($id);

        if (empty($usuario)) {
            header('location: /home/index');
            exit();
        }
    
        $estPartidas = $this->estadisticasDao->obtenerEstadisticasPartidasUsuario($id);
        $estUsuario = $this->estadisticasDao->obtenerRatioUsuario($id);

        $totalResp = $estUsuario['total_respondidas'] ?? 0;
        $totalCorrectas = $estUsuario['total_correctas'] ?? 0;

        $porcentajeAcierto = ($totalResp > 0)
            ? round(($totalCorrectas / $totalResp) * 100, 2)
            : 0;
        $usuarioPerfil = array_merge($usuario, [
            "partidas_jugadas" => $estPartidas['partidas_jugadas'] ?? 0,
            "partidas_ganadas" => $estPartidas['partidas_ganadas'] ?? 0,
            "partidas_perdidas" => $estPartidas['partidas_perdidas'] ?? 0,

            "total_respondidas" => $totalResp,
            "total_correctas" => $totalCorrectas,
            "porcentaje_acierto" => $porcentajeAcierto
        ]);
        $qr = null;
        try {
            if (!class_exists('QrGenerator')) {
                throw new Exception('La clase QrGenerator no está disponible.');
            }
            $qr = QrGenerator::generateQr($usuarioPerfil['id']);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        $this->mustacheRenderer->render(
            "perfilUsuario",
            [
                "usuario" => $usuarioPerfil,
                "qr" => $qr,
                "isOwner" => $isOwner
            ]
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