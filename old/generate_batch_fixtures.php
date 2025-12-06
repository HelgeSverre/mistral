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
$batchFileId = $resources['resource_ids']['batch_file'] ?? null;

if (! $batchFileId) {
    echo "Error: No batch_file ID found in resources.\n";
    exit(1);
}

echo "Generating batch fixtures using batch file ID: {$batchFileId}\n\n";

// Helper function to save fixture
function saveFixture(string $name, $response): void
{
    $fixtureDir = __DIR__.'/tests/Fixtures/Saloon/batch';
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
    // 1. Create batch job
    echo "1. Creating batch job with file {$batchFileId}...\n";
    $batchJobIn = new \HelgeSverre\Mistral\Dto\Batch\BatchJobIn(
        inputFiles: [$batchFileId],
        endpoint: '/v1/chat/completions',
        model: 'mistral-small-latest'
    );
    $response = $mistral->batch()->create($batchJobIn);

    if ($response->successful()) {
        saveFixture('create', $response);
        $data = json_decode($response->body(), true);
        $batchJobId = $data['id'];
        echo "   Created batch job ID: {$batchJobId}\n";

        // 2. Get batch job details
        echo "\n2. Getting batch job details for {$batchJobId}...\n";
        sleep(1); // Brief delay
        $getResponse = $mistral->batch()->get($batchJobId);
        if ($getResponse->successful()) {
            saveFixture('get', $getResponse);
        } else {
            echo '❌ Failed to get batch job: '.$getResponse->status()."\n";
        }

        // 3. List batch jobs
        echo "\n3. Listing batch jobs...\n";
        $listResponse = $mistral->batch()->list();
        if ($listResponse->successful()) {
            saveFixture('list', $listResponse);
        } else {
            echo '❌ Failed to list batch jobs: '.$listResponse->status()."\n";
        }

        // 4. Cancel batch job
        echo "\n4. Canceling batch job {$batchJobId}...\n";
        sleep(1); // Brief delay
        $cancelResponse = $mistral->batch()->cancel($batchJobId);
        if ($cancelResponse->successful()) {
            saveFixture('cancel', $cancelResponse);
        } else {
            echo '❌ Failed to cancel batch job: '.$cancelResponse->status().' - '.$cancelResponse->body()."\n";
        }

        echo "\n✅ All batch fixtures generated successfully!\n";
    } else {
        echo '❌ Failed to create batch job: '.$response->status().' - '.$response->body()."\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "\n❌ Error: ".$e->getMessage()."\n";
    exit(1);
}
