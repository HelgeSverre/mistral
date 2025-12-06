<?php

/**
 * Simple Resource Creator for Test Fixtures
 *
 * Creates real API resources that tests will use to generate fixtures.
 * Run tests separately with: composer test
 */

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use HelgeSverre\Mistral\Dto\Agents\AgentCreationRequest;
use HelgeSverre\Mistral\Dto\Batch\BatchJobIn;
use HelgeSverre\Mistral\Dto\Conversations\ConversationRequest;
use HelgeSverre\Mistral\Enums\FilePurpose;
use HelgeSverre\Mistral\Enums\Role;
use HelgeSverre\Mistral\Mistral;

// Load API key from .env
$envFile = __DIR__.'/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/MISTRAL_API_KEY="?([^"\n]+)"?/', $envContent, $matches)) {
        $apiKey = trim($matches[1], '"');
    }
}

if (empty($apiKey)) {
    echo "❌ Error: MISTRAL_API_KEY not found in .env\n";
    exit(1);
}

$mistral = new Mistral(apiKey: $apiKey);
$resources = [];

echo "=======================================================================\n";
echo "  Creating Test Resources for Fixture Generation\n";
echo "=======================================================================\n\n";

// Files
echo "📂 PHASE 1: Files\n";
echo str_repeat('-', 70)."\n";
try {
    $trainingFile = __DIR__.'/tests/Fixtures/test-data/training-data.jsonl';
    $response = $mistral->files()->upload($trainingFile, FilePurpose::FINE_TUNE);
    $fileId = $response->json('id');
    $resources['training_file'] = $fileId;
    echo "✅ Training file: {$fileId}\n";
} catch (Throwable $e) {
    echo '⚠️  Failed: '.$e->getMessage()."\n";
}

try {
    $batchFile = __DIR__.'/tests/Fixtures/test-data/batch-input.jsonl';
    $response = $mistral->files()->upload($batchFile, FilePurpose::BATCH);
    $fileId = $response->json('id');
    $resources['batch_file'] = $fileId;
    echo "✅ Batch file: {$fileId}\n";
} catch (Throwable $e) {
    echo '⚠️  Failed: '.$e->getMessage()."\n";
}

// Agents
echo "\n🤖 PHASE 2: Agents\n";
echo str_repeat('-', 70)."\n";
try {
    $request = new AgentCreationRequest(
        name: 'Test Agent',
        model: 'mistral-large-latest',
        instructions: 'You are a helpful test agent.',
        temperature: 0.7
    );
    $response = $mistral->agents()->create($request);
    $agentId = $response->json('id');
    $resources['agent'] = $agentId;
    echo "✅ Agent: {$agentId}\n";
} catch (Throwable $e) {
    echo '⚠️  Failed: '.$e->getMessage()."\n";
}

// Conversations
echo "\n💬 PHASE 3: Conversations\n";
echo str_repeat('-', 70)."\n";
try {
    $request = new ConversationRequest(
        model: 'mistral-large-latest',
        messages: [['role' => Role::user->value, 'content' => 'Hello!']]
    );
    $response = $mistral->conversations()->create($request);
    $convId = $response->json('id');
    $resources['conversation_model'] = $convId;
    echo "✅ Model conversation: {$convId}\n";
} catch (Throwable $e) {
    echo '⚠️  Failed: '.$e->getMessage()."\n";
}

if (isset($resources['agent'])) {
    try {
        $request = new ConversationRequest(
            agentId: $resources['agent'],
            messages: [['role' => Role::user->value, 'content' => 'Hello agent!']]
        );
        $response = $mistral->conversations()->create($request);
        $convId = $response->json('id');
        $resources['conversation_agent'] = $convId;
        echo "✅ Agent conversation: {$convId}\n";
    } catch (Throwable $e) {
        echo '⚠️  Failed: '.$e->getMessage()."\n";
    }
}

// Batch Jobs
echo "\n📦 PHASE 4: Batch Jobs\n";
echo str_repeat('-', 70)."\n";
if (isset($resources['batch_file'])) {
    try {
        $request = new BatchJobIn(
            inputFiles: [$resources['batch_file']],
            endpoint: '/v1/chat/completions',
            model: 'mistral-large-latest'
        );
        $response = $mistral->batch()->create($request);
        $jobId = $response->json('id');
        $resources['batch_job'] = $jobId;
        echo "✅ Batch job: {$jobId}\n";
    } catch (Throwable $e) {
        echo '⚠️  Failed: '.$e->getMessage()."\n";
    }
} else {
    echo "⏭️  Skipped (no batch file)\n";
}

// Save resource IDs
$data = [
    'generated_at' => date('Y-m-d H:i:s'),
    'resource_ids' => $resources,
];

file_put_contents(__DIR__.'/test_resources.json', json_encode($data, JSON_PRETTY_PRINT));

echo "\n".str_repeat('=', 70)."\n";
echo "✅ Resources Created!\n";
echo str_repeat('=', 70)."\n\n";

echo "📝 Resource IDs:\n";
foreach ($resources as $key => $id) {
    echo "   {$key}: {$id}\n";
}

echo "\n💾 Saved to: test_resources.json\n";
echo "\n🧪 Next step: Run tests to generate fixtures\n";
echo "   composer test\n\n";
echo "🧹 Cleanup when done:\n";
echo "   php cleanup_resources.php\n\n";
