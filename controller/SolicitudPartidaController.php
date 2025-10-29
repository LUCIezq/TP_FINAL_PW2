<?php

class SolicitudPartidaController
{
    private SolicitudPartidaDao $solicitudPartidaDao;

    public function __construct(SolicitudPartidaDao $solicitudPartidaDao)
    {
        $this->solicitudPartidaDao = $solicitudPartidaDao;
    }

    public function crearSolicitudPartida()
    {

        $errors = [];

        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        $params = [
            'id_emisor' => $_SESSION['user']['id'],
            'id_receptor' => $_POST['destinatario_id']
        ];
        try {
            $errors = $this->solicitudPartidaDao->validarSolicitud($params);

            if (!empty($errors)) {
                $_SESSION['solicitud_errors'] = $errors;

            } else {
                $_SESSION['solicitud_success'] = "Solicitud de partida enviada correctamente.";
            }
        } catch (Exception $e) {
            $_SESSION['solicitud_errors'] = ["Error al procesar la solicitud: " . $e->getMessage()];
        }

        header("location: /home/index/");
        exit();
    }
}