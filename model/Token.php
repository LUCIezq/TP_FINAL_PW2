<?php

class Token
{
    private string $token;

    public function __construct($length = 16)
    {
        $this->token = bin2hex(random_bytes($length));
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
