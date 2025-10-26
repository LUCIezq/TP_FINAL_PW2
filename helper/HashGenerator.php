<?php

class HashGenerator
{
    public static function generateHash(string $input): string
    {
        return password_hash($input, PASSWORD_BCRYPT);
    }

    public static function verifyHash(string $input, string $hash): bool
    {
        return password_verify($input, $hash);
    }
}
