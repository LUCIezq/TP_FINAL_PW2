<?php
class Genero
{
    private ?int $id;
    private string $nombre;

    public function __construct($nombre)
    {
        $this->nombre = $nombre;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getNombre(): string
    {
        return $this->nombre;
    }
}
