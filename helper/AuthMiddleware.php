<?php
class AuthMiddleware
{
    public static function verificar($controlador, $metodo)
    {

        if (!isset($_SESSION['usuario'])) {
            header('Location: /login/index');
            exit;
        }

        $rolUsuario = $_SESSION['usuario']['rol'] ?? null;

        $permisos = require 'helper/Permisos.php';

        if (!isset($permisos[$controlador]))
            return;

        if (isset($permisos[$controlador][$metodo])) {
            $rolesPermitidos = $permisos[$controlador][$metodo];
            if (!in_array($rolUsuario, $rolesPermitidos)) {
                header('Location: /error403');
                exit;
            }
        }
    }
}
