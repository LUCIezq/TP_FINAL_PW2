<?php
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class QrGenerator
{
    private $config = parse_ini_file("config/config.ini", true);
    private static string $BASE_URL = $config['appProd']['url'];


    public static function generateQr($data = "")
    {
        $writer = new PngWriter();

        $qrCode = new QrCode(
            data: self::$BASE_URL . $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        return $writer->write($qrCode)->getDataUri();
    }
}