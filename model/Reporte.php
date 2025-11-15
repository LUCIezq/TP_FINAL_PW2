<?php

class Reporte
{
    private $motivo;
    private $detalle;
    private $pregunta_id;
    private $usuario_id;

    public function __construct($motivo, $detalle, $pregunta_id, $usuario_id)
    {
        $this->motivo = $motivo;
        $this->detalle = $detalle;
        $this->pregunta_id = $pregunta_id;
        $this->usuario_id = $usuario_id;
    }

    public function getMotivo()
    {
        return $this->motivo;
    }

    public function getDetalle()
    {
        return $this->detalle;
    }

    public function getPreguntaId()
    {
        return $this->pregunta_id;
    }

    public function getUsuarioId()
    {
        return $this->usuario_id;
    }
}