<?php
$data = json_encode([
    'email' => 'admin@archive.com',
    'passwordHash' => 'admin123'
]);

$ch = curl_init('http://localhost:8080/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
echo "cURL Error: " . $curlError . "<br>";
echo "Përgjigja nga Serveri: " . $response;
?>