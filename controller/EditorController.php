<?php
class EditorController
{

    private EditorDao $dao;
    private MustacheRenderer $mustacheRenderer;

    public function __construct(EditorDao $dao, MustacheRenderer $mustacheRenderer)
    {
        $this->dao = $dao;
        $this->mustacheRenderer = $mustacheRenderer;
    }

    public function index()
    {
        $this->mustacheRenderer->render("editor", []);
    }


}