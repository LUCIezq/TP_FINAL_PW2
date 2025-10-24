<?php

class HashGenerator
{
    public static function generateHash(string $input): string
    {
        return password_hash($input, PASSWORD_BCRYPT);
    }
}
