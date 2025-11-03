<?php

$ciudad = $_GET["city"] ?? null;
$pais = $_GET["country"] ?? null;

$apiUrl = "https://nominatim.openstreetmap.org/search?city=" . urlencode($ciudad) . "&country=" . urlencode($pais) . "&format=json&limit=1";

$opts = [
    'http' => [
        'header' => "User-Agent: MiProyectoFinalPW2/1.0 (lucieze02@icloud.com)\r\n"
    ]
];

$context = stream_context_create($opts);

$response = @file_get_contents($apiUrl, false, $context);

if ($response === FALSE) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al contactar con la API externa.']);
} else {
    header('Content-Type: application/json');
    echo $response;
}