<?php

class Token
{
    private string $token;
    private string $expiracion;

    public function __construct($length = 16, $expiration = '+1 day')
    {
        $this->token = bin2hex(random_bytes($length));
        $this->expiracion = date('Y-m-d H:i:s', strtotime($expiration));
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiracion(): string
    {
        return $this->expiracion;
    }
}
