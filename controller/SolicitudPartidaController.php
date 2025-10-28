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
        var_dump($_POST);
    }
}