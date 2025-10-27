<?php
include_once "helper/StartSession.php";
class CreateUserSession
{
    public static function create($user): void
    {
        unset($user['contrasena']);
        unset($user['token_verificacion']);

        StartSession::start();

        $_SESSION['user'] = $user;
        $_SESSION['logged_in'] = true;
    }
}