<?php
header('Content-Type: application/json; charset=utf-8');

$lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
$lon = filter_input(INPUT_GET, 'lon', FILTER_VALIDATE_FLOAT);
if ($lat === false || $lon === false) {
    http_response_code(400);
    echo json_encode(['error' => 'coords_invalid']);
    exit;
}

$url = sprintf(
    'https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&lat=%s&lon=%s',
    rawurlencode((string) $lat),
    rawurlencode((string) $lon)
);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => [
        'User-Agent: TP_FINAL_PW2/1.0 (tu-email@example.com)',
        'Accept: application/json'
    ]
]);
$resp = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err || $code !== 200 || !$resp) {
    http_response_code(502);
    echo json_encode(['error' => 'geocoder_failed']);
    exit;
}

$data = json_decode($resp, true);
echo json_encode([
    'address' => [
        'country' => $data['address']['country'] ?? null,
        'city' => $data['address']['city'] ?? ($data['address']['town'] ?? ($data['address']['village'] ?? null)),
        'state' => $data['address']['state'] ?? null
    ],
    'raw' => $data
]);
