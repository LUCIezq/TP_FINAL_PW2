<?php

class IsLogged
{
    public static function isLogged()
    {
        return $_SESSION['logged_in'] && isset($_SESSION['user']);
    }
}