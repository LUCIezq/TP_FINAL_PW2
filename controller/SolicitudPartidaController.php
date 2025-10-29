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
        ShowData::show($_POST);

        //Consideraciones a tener en cuenta a la hora de mandar una peticion

        //1- Validar que el usuario este logueado.
        //2- Validar que lo que me llega por get es valido.
        //3- Que exista el usuario para enviar la peticion
        //4- Validar que ya no exista una peticion previa con un estado pendiente o aceptada.
        //5- Que el id del emisor no sea igual al receptor. 
    }
}