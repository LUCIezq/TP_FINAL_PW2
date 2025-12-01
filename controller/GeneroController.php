<?php

class GeneroController
{

    private CategoryDao $categoryDao;

    public function __construct(CategoryDao $categoryDao)
    {
        $this->categoryDao = $categoryDao;
    }

    public function listarGenerosJSON()
    {
        header('Content-Type:application/json');

        try {

            $generos = $this->categoryDao->getAll();
            echo json_encode($generos);

        } catch (Exception $e) {
            // siempre devolver algo aunque
            http_response_code(500);
            echo json_encode([
                'error' => 'Error al obtener los géneros',
                'message' => $e->getMessage()
            ]);
        }
    }
}