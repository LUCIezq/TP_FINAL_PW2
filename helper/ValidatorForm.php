<?php
class ValidatorForm
{

    public static function isFieldEmpty($field)
    {
        return empty(trim($field));
    }

    public static function isNumeric($field)
    {
        return is_numeric($field);
    }

    public static function isEmailValid($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function isPasswordValid($password, $longitud)
    {
        return strlen($password) >= $longitud;
    }

    public static function isNull($object)
    {
        return is_null($object);
    }

    public static function doPasswordsMatch($password, $confirmPassword)
    {
        return $password === $confirmPassword;
    }
}
