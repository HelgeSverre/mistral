<?php

require_once __DIR__.'/../vendor/autoload.php';

use HelgeSverre\Mistral\Mistral;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

$mistral = new Mistral(apiKey: $_ENV['MISTRAL_API_KEY']);

// Process a PDF from URL
echo "Processing PDF from URL...\n";
$response = $mistral->ocr()->processUrl(
    url: 'https://pdfa.org/download-area/cheat-sheets/Color.pdf',
    includeImageBase64: false
);

if ($response->successful()) {
    $data = $response->json();

    echo "Model: {$data['model']}\n";
    echo "Pages processed: {$data['usage_info']['pages_processed']}\n";
    echo 'Document size: '.number_format($data['usage_info']['doc_size_bytes'])." bytes\n\n";

    foreach ($data['pages'] as $page) {
        echo "Page {$page['index']}:\n";
        echo '- Images found: '.count($page['images'])."\n";
        echo "- Dimensions: {$page['dimensions']['width']}x{$page['dimensions']['height']} @ {$page['dimensions']['dpi']} DPI\n";
        echo "- Markdown preview:\n";
        echo substr($page['markdown'], 0, 200)."...\n\n";
    }
} else {
    echo 'Error: '.$response->json('message')."\n";
}
