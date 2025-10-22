<?php

$lat = $_GET['lat'] ?? null;
$lon = $_GET['lon'] ?? null;

const baseUrl = "";

if (!is_numeric($lat) || !is_numeric($lon)) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

$api_url = "https://nominatim.openstreetmap.org/reverse?lat=" . urlencode($lat) . "&lon=" . urlencode($lon) . "&format=json";

$opts = [
    'http' => [
        'header' => "User-Agent: MiProyectoFinalPW2/1.0 (lucieze02@icloud.com)\r\n"
    ]
];

$context = stream_context_create($opts);

$response = @file_get_contents($api_url, false, $context);

if ($response === FALSE) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al contactar con la API externa.']);
} else {
    header('Content-Type: application/json');
    echo $response;
}
