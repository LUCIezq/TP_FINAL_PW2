<?php
class RegisterController
{
    private GeneroDao $generoDao;
    private MustacheRenderer $renderer;

    public function __construct(GeneroDao $generoDao, MustacheRenderer $renderer)
    {
        $this->generoDao = $generoDao;
        $this->renderer = $renderer;
    }

    public function index()
    {
        $genders = $this->generoDao->getAllGenders() ?? [];
        $this->renderer->render("register", ["genders" => $genders]);
    }
}
