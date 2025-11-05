<?php
class GuestMiddleware
{
    public static function verificar()
    {
        session_start();

        if (isset($_SESSION['usuario'])) {
            $rol = $_SESSION['usuario']['rol'];
            switch ($rol) {
                case UserRole::ADMIN:
                    header('Location: /admin/dashboard');
                    break;
                case UserRole::EDITOR:
                    header('Location: /editor/home');
                    break;
                case UserRole::JUGADOR:
                default:
                    header('Location: /home');
                    break;
            }
            exit;
        }
    }
}