<?php

class FileUploader
{
    private static string $uploadFileDir = 'uploads/profiles/';
    private static array $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg'];

    public static function uploadFile(string $inputName, string $uniqueIdentifier): ?string
    {
        if (!self::ensureUploadDirectoryExists()) {

            error_log("Error: El directorio de subida no existe o no tiene permisos de escritura: " . self::$uploadFileDir);
            return null;
        }


        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {

            return null;
        }

        $fileTmpPath = $_FILES[$inputName]['tmp_name'];
        $fileName = $_FILES[$inputName]['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, self::$allowedfileExtensions)) {
            return null;
        }

        $newFileName = md5($uniqueIdentifier . time()) . '.' . $fileExtension;
        $dest_path = self::$uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            return $dest_path;
        }

        return null;
    }

    private static function ensureUploadDirectoryExists(): bool
    {
        $dir = self::$uploadFileDir;


        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return false;
            }
        }

        return is_writable($dir);
    }
}
