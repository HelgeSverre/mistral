<?php

require __DIR__.'/vendor/autoload.php';

use HelgeSverre\Mistral\Dto\Libraries\DocumentUpdateIn;
use HelgeSverre\Mistral\Dto\Libraries\LibraryIn;
use HelgeSverre\Mistral\Dto\Libraries\LibraryInUpdate;
use HelgeSverre\Mistral\Mistral;
use Illuminate\Contracts\Console\Kernel;

// Bootstrap Laravel for testing
$app = require_once __DIR__.'/vendor/orchestra/testbench-core/laravel/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

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

// Helper function to save fixture
function saveFixture(string $name, $response): void
{
    $fixtureDir = __DIR__.'/tests/Fixtures/Saloon/libraries';
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

echo "Generating library fixtures...\n\n";

try {
    // 1. List libraries (should be empty initially)
    echo "1. Listing libraries...\n";
    $response = $mistral->libraries()->list();
    if ($response->successful()) {
        saveFixture('list-libraries', $response);
    }

    // 2. Create a library
    echo "\n2. Creating a test library...\n";
    $libraryIn = new LibraryIn(
        name: 'Test Library',
        description: 'A test library for fixture generation'
    );
    $response = $mistral->libraries()->create($libraryIn);

    if ($response->successful()) {
        saveFixture('create-library', $response);
        $data = json_decode($response->body(), true);
        $libraryId = $data['id'];
        echo "   Created library ID: {$libraryId}\n";

        // 3. Get library details
        echo "\n3. Getting library details...\n";
        sleep(1);
        $response = $mistral->libraries()->get($libraryId);
        if ($response->successful()) {
            saveFixture('get-library', $response);
        }

        // 4. Update library
        echo "\n4. Updating library...\n";
        $libraryUpdate = new LibraryInUpdate(
            name: 'Updated Test Library',
            description: 'Updated description'
        );
        $response = $mistral->libraries()->update($libraryId, $libraryUpdate);
        if ($response->successful()) {
            saveFixture('update-library', $response);
        }

        // 5. List documents (should be empty)
        echo "\n5. Listing documents in library...\n";
        $response = $mistral->libraries()->listDocuments($libraryId);
        if ($response->successful()) {
            saveFixture('list-documents', $response);
        }

        // 6. Upload a test document
        echo "\n6. Uploading a test document...\n";
        $tempFile = tempnam(sys_get_temp_dir(), 'test_doc_');
        file_put_contents($tempFile, 'This is a test document for library testing.');

        try {
            $response = $mistral->libraries()->uploadDocument($libraryId, $tempFile);

            if ($response->successful()) {
                saveFixture('upload-document', $response);
                $docData = json_decode($response->body(), true);
                $documentId = $docData['id'];
                echo "   Uploaded document ID: {$documentId}\n";

                // 7. Get document
                echo "\n7. Getting document details...\n";
                sleep(2); // Wait for processing
                $response = $mistral->libraries()->getDocument($libraryId, $documentId);
                if ($response->successful()) {
                    saveFixture('get-document', $response);
                }

                // 8. Update document
                echo "\n8. Updating document metadata...\n";
                $documentUpdate = new DocumentUpdateIn(
                    name: 'renamed-test-document.txt'
                );
                $response = $mistral->libraries()->updateDocument($libraryId, $documentId, $documentUpdate);
                if ($response->successful()) {
                    saveFixture('update-document', $response);
                }

                // Clean up: Delete document
                echo "\n9. Cleaning up - deleting document...\n";
                $mistral->libraries()->deleteDocument($libraryId, $documentId);
            }
        } finally {
            unlink($tempFile);
        }

        // Clean up: Delete library
        echo "\n10. Cleaning up - deleting library...\n";
        $mistral->libraries()->delete($libraryId);

        echo "\n✅ All library fixtures generated successfully!\n";
    } else {
        echo '❌ Failed to create library: '.$response->status().' - '.$response->body()."\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "\n❌ Error: ".$e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
    exit(1);
}
