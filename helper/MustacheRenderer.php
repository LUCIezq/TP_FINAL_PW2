<?php

class MustacheRenderer
{
    private $mustache;
    private $viewsFolder;

    public function __construct($partialsPathLoader)
    {
        $this->mustache = new \Mustache_Engine([
            'partials_loader' => new \Mustache_Loader_FilesystemLoader($partialsPathLoader)
        ]);

        $this->viewsFolder = $partialsPathLoader;
    }

    public function render($contentFile, $data = [])
    {
         //NUEVOecho $this->generateHtml($this->viewsFolder . '/' . $contentFile . ".mustache", $data);

        echo $this->generateHtml($this->viewsFolder . '/' . $contentFile . "Vista.mustache", $data);

    }

    private function generateHtml($contentFile, $data = [])
    {
        $contentAsString = file_get_contents($this->viewsFolder . '/header.mustache');
        $contentAsString .= file_get_contents($contentFile);
        $contentAsString .= file_get_contents($this->viewsFolder . '/footer.mustache');

        return $this->mustache->render($contentAsString, $data);
    }
}
