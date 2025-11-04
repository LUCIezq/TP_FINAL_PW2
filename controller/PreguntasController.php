<?php

class PreguntasController
{

    private MustacheRenderer $mustacheRenderer;
    private MyConexion $conexion;
    private CategoryDao $categoryDao;


    public function __construct(MustacheRenderer $mustacheRenderer, MyConexion $conexion, CategoryDao $categoryDao)
    {
        $this->mustacheRenderer = $mustacheRenderer;
        $this->conexion = $conexion;
        $this->categoryDao = $categoryDao;
    }
    public function index()
    {
        if (!IsLogged::isLogged()) {
            header('location: /login/index');
            exit();
        }

        $categories = $this->categoryDao->getAll();

        $this->mustacheRenderer->render("preguntas", ["categories" => $categories]);

    }
}