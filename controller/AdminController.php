<?php
class AdminController
{
    private MustacheRenderer $mustacheRenderer;

    public function __construct(MustacheRenderer $mustacheRenderer)
    {
        $this->mustacheRenderer = $mustacheRenderer;

    }
    public function index()
    {
        $this->mustacheRenderer->render("admin", []);
    }
}