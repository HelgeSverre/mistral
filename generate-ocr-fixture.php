<?php

require_once __DIR__.'/vendor/autoload.php';

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? null;
if (! $apiKey) {
    exit("No API key found\n");
}

// Test with the real PDF URL
$ch = curl_init('https://api.mistral.ai/v1/ocr');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer '.$apiKey,
    'Content-Type: application/json',
]);

$payload = [
    'model' => 'mistral-ocr-latest',
    'document' => [
        'type' => 'document_url',
        'document_url' => 'https://pdfa.org/download-area/cheat-sheets/Color.pdf',
    ],
    'include_image_base64' => false,
];

echo "Sending request to OCR API...\n";
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headers = curl_getinfo($ch);
curl_close($ch);

echo "Response status: $httpCode\n";

// Create fixture format
$fixture = [
    'statusCode' => $httpCode,
    'headers' => [
        'Content-Type' => 'application/json',
    ],
    'data' => json_decode($response, true),
];

// Save as a new fixture for successful response
if ($httpCode === 200) {
    file_put_contents(__DIR__.'/tests/Fixtures/Saloon/ocr.processDocumentSuccess.json', json_encode($fixture, JSON_PRETTY_PRINT));
    echo "Saved successful response fixture to ocr.processDocumentSuccess.json\n";
} else {
    echo "Error response:\n";
    echo json_encode(json_decode($response), JSON_PRETTY_PRINT)."\n";
}
