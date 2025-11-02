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

    public function rechazar()
    {
        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        $solicitudId = (int) $_POST['solicitud_id'];

        if (empty($solicitudId) || !is_numeric($solicitudId)) {
            header("location:/home/index/");
            exit();
        }

        try {
            $rechazada = $this->solicitudPartidaDao->rechazarSolicitud($solicitudId);

            ShowData::show($rechazada);

            if (!$rechazada) {
                $_SESSION['solicitud_errors'] = "Error al rechazar la solicitud.";
            } else {
                $_SESSION['solicitud_success'] = "Solicitud rechazada correctamente.";
            }

            header("location: /home/index/");
            exit();
        } catch (Exception $e) {
            $_SESSION['solicitud_errors'] = ["Error al procesar la solicitud: " . $e->getMessage()];
        }
    }

    public function aceptar()
    {
        if (!IsLogged::isLogged()) {
            header("location:/login/index");
            exit();
        }

        $solicitudId = (int) $_POST["solicitud_id"];

        if (empty($solicitudId) || !is_numeric($solicitudId)) {
            header("location:/home/index/");
            exit();
        }

        try {
            

        } catch (Exception $e) {

        }

    }
}