<?php
class CreateUserSession
{
    public static function create($user): void
    {
        unset($user['contrasena']);
        unset($user['token_verificacion']);

        $_SESSION['user'] = $user;
        $_SESSION['logged_in'] = true;
    }
}