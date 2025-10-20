<?php
class Logger
{

    private $dir;

    public function __construct($dir = '/logs')
    {
        $this->dir = $dir;
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
    }
    public function log($message, $level = 'INFO')
    {
        $date = new DateTime();
        $timestamp = $date->format('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($this->dir . '/app.log', $logMessage, FILE_APPEND);
    }
    public function info($mensaje)
    {
        $this->log($mensaje, 'INFO');
    }

    public function warning($mensaje)
    {
        $this->log($mensaje, 'WARNING');
    }

    public function error($mensaje)
    {
        $this->log($mensaje, 'ERROR');
    }

    public function critical($mensaje)
    {
        $this->log($mensaje, 'CRITICAL');
    }
}
