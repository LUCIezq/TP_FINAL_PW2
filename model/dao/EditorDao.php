<?php

class EditorDao
{

    private MyConexion $conexion;

    public function __construct(MyConexion $conexion)
    {
        $this->conexion = $conexion;
    }
}