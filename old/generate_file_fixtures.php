<?php

require __DIR__.'/vendor/autoload.php';

use HelgeSverre\Mistral\Mistral;

// Load environment variables
if (file_exists(__DIR__.'/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');

if (empty($apiKey)) {
    echo "Error: MISTRAL_API_KEY not found in environment variables.\n";
    exit(1);
}

$mistral = new Mistral(apiKey: $apiKey);

// Load created resources
$resourcesFile = __DIR__.'/created_resources.json';
if (! file_exists($resourcesFile)) {
    echo "Error: created_resources.json not found. Run create_test_resources.php first.\n";
    exit(1);
}

$resources = json_decode(file_get_contents($resourcesFile), true);
$fileId = $resources['resource_ids']['training_file'] ?? null;

if (! $fileId) {
    echo "Error: No training_file ID found in resources.\n";
    exit(1);
}

echo "Generating file fixtures using file ID: {$fileId}\n\n";

// Helper function to save fixture
function saveFixture(string $name, $response): void
{
    $fixtureDir = __DIR__.'/tests/Fixtures/Saloon/files';
    if (! is_dir($fixtureDir)) {
        mkdir($fixtureDir, 0755, true);
    }

    $fixture = [
        'statusCode' => $response->status(),
        'headers' => $response->headers()->all(),
        'data' => $response->body(),
        'context' => [],
    ];

    $filePath = "{$fixtureDir}/{$name}.json";
    file_put_contents($filePath, json_encode($fixture, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "✅ Saved: {$filePath}\n";
}

try {
    // 1. List files
    echo "1. Listing files...\n";
    $response = $mistral->files()->list();
    if ($response->successful()) {
        saveFixture('list', $response);
    } else {
        echo '❌ Failed to list files: '.$response->status()."\n";
    }

    // 2. Retrieve file
    echo "\n2. Retrieving file {$fileId}...\n";
    $response = $mistral->files()->retrieve($fileId);
    if ($response->successful()) {
        saveFixture('retrieve', $response);
    } else {
        echo '❌ Failed to retrieve file: '.$response->status()."\n";
    }

    // 3. Get signed URL
    echo "\n3. Getting signed URL for file {$fileId}...\n";
    $response = $mistral->files()->getSignedUrl($fileId);
    if ($response->successful()) {
        saveFixture('signedUrl', $response);
    } else {
        echo '❌ Failed to get signed URL: '.$response->status()."\n";
    }

    // 4. Upload - we'll use a properly formatted training file
    echo "\n4. Uploading a new test file...\n";
    $tempFile = tempnam(sys_get_temp_dir(), 'mistral_test_');
    $trainingData = [
        ['messages' => [
            ['role' => 'user', 'content' => 'What is machine learning?'],
            ['role' => 'assistant', 'content' => 'Machine learning is a subset of artificial intelligence.'],
        ]],
        ['messages' => [
            ['role' => 'user', 'content' => 'Explain neural networks'],
            ['role' => 'assistant', 'content' => 'Neural networks are computing systems inspired by biological neural networks.'],
        ]],
    ];

    foreach ($trainingData as $item) {
        file_put_contents($tempFile, json_encode($item)."\n", FILE_APPEND);
    }

    try {
        $response = $mistral->files()->upload(
            filePath: $tempFile,
            purpose: HelgeSverre\Mistral\Enums\FilePurpose::FINE_TUNE
        );

        if ($response->successful()) {
            saveFixture('upload', $response);
            $uploadedFileId = $response->dto()->data->id;
            echo "   Uploaded file ID: {$uploadedFileId}\n";

            // 5. Delete the uploaded file
            echo "\n5. Deleting test file {$uploadedFileId}...\n";
            $deleteResponse = $mistral->files()->delete($uploadedFileId);
            if ($deleteResponse->successful()) {
                saveFixture('delete', $deleteResponse);
            } else {
                echo '❌ Failed to delete file: '.$deleteResponse->status()."\n";
            }
        } else {
            echo '❌ Failed to upload file: '.$response->status().' - '.$response->body()."\n";
        }
    } finally {
        unlink($tempFile);
    }

    echo "\n✅ All file fixtures generated successfully!\n";

} catch (Exception $e) {
    echo "\n❌ Error: ".$e->getMessage()."\n";
    exit(1);
}
