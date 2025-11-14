<?php

class CategoriaController
{

    private CategoryDao $categoryDao;

    public function __construct(CategoryDao $categoryDao)
    {
        $this->categoryDao = $categoryDao;
    }

    public function eliminarCategoria()
    {

    }

    public function getAll(): array
    {
        return $this->categoryDao->getAll();
    }
}