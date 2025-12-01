<?php
class SendJSON
{
    public static function procesarJSON($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
