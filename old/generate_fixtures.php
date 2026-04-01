<?php

/**
 * Comprehensive Fixture Generation Script
 *
 * This script creates real Mistral API resources in the correct dependency order
 * to generate valid test fixtures for the entire test suite.
 *
 * Usage: php generate_fixtures.php
 *
 * WARNING: This will make real API calls and may incur costs!
 */

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use HelgeSverre\Mistral\Dto\Agents\AgentCreationRequest;
use HelgeSverre\Mistral\Dto\Batch\BatchJobIn;
use HelgeSverre\Mistral\Dto\Conversations\ConversationRequest;
use HelgeSverre\Mistral\Enums\FilePurpose;
use HelgeSverre\Mistral\Enums\Role;
use HelgeSverre\Mistral\Mistral;

class FixtureGenerator
{
    private Mistral $mistral;

    private array $createdResources = [];

    private array $resourceIds = [];

    private bool $dryRun = false;

    public function __construct(string $apiKey, bool $dryRun = false)
    {
        $this->mistral = new Mistral(apiKey: $apiKey);
        $this->dryRun = $dryRun;
    }

    public function generate(): void
    {
        $this->log('='.str_repeat('=', 70));
        $this->log('  Mistral PHP SDK - Comprehensive Fixture Generator');
        $this->log('='.str_repeat('=', 70));
        $this->log('');

        if ($this->dryRun) {
            $this->log('⚠️  DRY RUN MODE - No actual API calls will be made');
            $this->log('');
        }

        try {
            // Phase 1: Files
            $this->log("\n📂 PHASE 1: File Uploads");
            $this->log(str_repeat('-', 70));
            $this->uploadFiles();

            // Phase 2: Agents
            $this->log("\n🤖 PHASE 2: Agent Creation");
            $this->log(str_repeat('-', 70));
            $this->createAgents();

            // Phase 3: Conversations
            $this->log("\n💬 PHASE 3: Conversations");
            $this->log(str_repeat('-', 70));
            $this->createConversations();

            // Phase 4: Batch Jobs
            $this->log("\n📦 PHASE 4: Batch Jobs");
            $this->log(str_repeat('-', 70));
            $this->createBatchJobs();

            // Phase 5: Fine-tuning (SKIPPED - expensive)
            $this->log("\n⚙️  PHASE 5: Fine-tuning Jobs");
            $this->log(str_repeat('-', 70));
            $this->log('⏭️  Skipped (expensive, would incur costs)');
            $this->log('   Fine-tuning fixtures will use mock data or be manually created');

            // Phase 6: Audio (requires real audio file)
            $this->log("\n🎤 PHASE 6: Audio Transcription");
            $this->log(str_repeat('-', 70));
            $this->createAudioFixtures();

            // Phase 7: Summary
            $this->log("\n".str_repeat('=', 70));
            $this->log('✅ Fixture Generation Complete!');
            $this->log(str_repeat('=', 70));
            $this->printSummary();

            // Save resource IDs for cleanup
            $this->saveResourceIds();

        } catch (Throwable $e) {
            $this->log("\n❌ Error: ".$e->getMessage());
            $this->log('Stack trace: '.$e->getTraceAsString());
            exit(1);
        }
    }

    private function uploadFiles(): void
    {
        // Upload training data file
        $trainingFile = __DIR__.'/tests/Fixtures/test-data/training-data.jsonl';
        if (file_exists($trainingFile)) {
            $this->log('📤 Uploading training data file...');
            if (! $this->dryRun) {
                try {
                    $response = $this->mistral->files()->upload(
                        filePath: $trainingFile,
                        purpose: FilePurpose::FINE_TUNE
                    );
                    $fileId = $response->json('id');
                    $this->resourceIds['training_file'] = $fileId;
                    $this->createdResources[] = ['type' => 'file', 'id' => $fileId];
                    $this->log("   ✅ Training file uploaded: {$fileId}");
                } catch (Throwable $e) {
                    $this->log('   ⚠️  Failed: '.$e->getMessage());
                }
            }
        } else {
            $this->log("   ⚠️  Training data file not found: {$trainingFile}");
        }

        // Upload batch input file
        $batchFile = __DIR__.'/tests/Fixtures/test-data/batch-input.jsonl';
        if (file_exists($batchFile)) {
            $this->log('📤 Uploading batch input file...');
            if (! $this->dryRun) {
                try {
                    $response = $this->mistral->files()->upload(
                        filePath: $batchFile,
                        purpose: FilePurpose::BATCH
                    );
                    $fileId = $response->json('id');
                    $this->resourceIds['batch_file'] = $fileId;
                    $this->createdResources[] = ['type' => 'file', 'id' => $fileId];
                    $this->log("   ✅ Batch file uploaded: {$fileId}");
                } catch (Throwable $e) {
                    $this->log('   ⚠️  Failed: '.$e->getMessage());
                }
            }
        } else {
            $this->log("   ⚠️  Batch input file not found: {$batchFile}");
        }

        // Run file tests to generate fixtures
        $this->log('🧪 Running file tests to generate fixtures...');
        if (! $this->dryRun) {
            $this->runTests('tests/Resources/FilesTest.php');
        }
    }

    private function createAgents(): void
    {
        $this->log('🤖 Creating test agent...');

        if (! $this->dryRun) {
            try {
                $response = $this->mistral->agents()->create(
                    new AgentCreationRequest(
                        name: 'Test Agent for Fixtures',
                        model: 'mistral-large-latest',
                        instructions: 'You are a helpful test agent used for generating fixtures in the Mistral PHP SDK test suite.',
                        description: 'Test agent for fixture generation',
                        tools: [
                            [
                                'type' => 'function',
                                'function' => [
                                    'name' => 'get_current_time',
                                    'description' => 'Get the current time',
                                    'parameters' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'timezone' => [
                                                'type' => 'string',
                                                'description' => 'The timezone',
                                            ],
                                        ],
                                        'required' => ['timezone'],
                                    ],
                                ],
                            ],
                        ],
                        temperature: 0.7,
                        topP: 0.9
                    )
                );

                $agentId = $response->json('id');
                $this->resourceIds['agent'] = $agentId;
                $this->log("   ✅ Agent created: {$agentId}");
                $this->createdResources[] = ['type' => 'agent', 'id' => $agentId];

            } catch (Throwable $e) {
                $this->log('   ⚠️  Failed: '.$e->getMessage());
            }
        }

        // Run agent tests to generate fixtures
        $this->log('🧪 Running agent tests to generate fixtures...');
        if (! $this->dryRun) {
            $this->runTests('tests/Resources/AgentsTest.php');
        }
    }

    private function createConversations(): void
    {
        // Model-based conversation
        $this->log('💬 Creating model-based conversation...');
        if (! $this->dryRun) {
            try {
                $request = new ConversationRequest(
                    model: 'mistral-large-latest',
                    messages: [
                        [
                            'role' => Role::user->value,
                            'content' => 'Hello, this is a test conversation for generating fixtures',
                        ],
                    ]
                );

                $response = $this->mistral->conversations()->create($request);

                $convId = $response->json('id');
                $this->resourceIds['conversation_model'] = $convId;
                $this->log("   ✅ Model-based conversation: {$convId}");
                $this->createdResources[] = ['type' => 'conversation', 'id' => $convId];

            } catch (Throwable $e) {
                $this->log('   ⚠️  Failed: '.$e->getMessage());
            }
        }

        // Agent-based conversation (if agent was created)
        if (isset($this->resourceIds['agent'])) {
            $this->log('💬 Creating agent-based conversation...');
            if (! $this->dryRun) {
                try {
                    $request = new ConversationRequest(
                        agentId: $this->resourceIds['agent'],
                        messages: [
                            [
                                'role' => Role::user->value,
                                'content' => 'Hello agent, this is a test conversation',
                            ],
                        ]
                    );

                    $response = $this->mistral->conversations()->create($request);

                    $convId = $response->json('id');
                    $this->resourceIds['conversation_agent'] = $convId;
                    $this->log("   ✅ Agent-based conversation: {$convId}");
                    $this->createdResources[] = ['type' => 'conversation', 'id' => $convId];

                } catch (Throwable $e) {
                    $this->log('   ⚠️  Failed: '.$e->getMessage());
                }
            }
        }

        // Run conversation tests
        $this->log('🧪 Running conversation tests to generate fixtures...');
        if (! $this->dryRun) {
            $this->runTests('tests/Resources/ConversationsTest.php');
        }
    }

    private function createBatchJobs(): void
    {
        if (! isset($this->resourceIds['batch_file'])) {
            $this->log('   ⚠️  Skipped (no batch file uploaded)');

            return;
        }

        $this->log('📦 Creating batch job...');
        if (! $this->dryRun) {
            try {
                $request = new BatchJobIn(
                    inputFiles: [$this->resourceIds['batch_file']],
                    endpoint: '/v1/chat/completions',
                    model: 'mistral-large-latest'
                );

                $response = $this->mistral->batch()->create($request);

                $jobId = $response->json('id');
                $this->resourceIds['batch_job'] = $jobId;
                $this->log("   ✅ Batch job created: {$jobId}");
                $this->createdResources[] = ['type' => 'batch_job', 'id' => $jobId];

            } catch (Throwable $e) {
                $this->log('   ⚠️  Failed: '.$e->getMessage());
            }
        }

        // Run batch tests
        $this->log('🧪 Running batch tests to generate fixtures...');
        if (! $this->dryRun) {
            $this->runTests('tests/Resources/BatchTest.php');
        }
    }

    private function createAudioFixtures(): void
    {
        $audioFile = __DIR__.'/tests/Fixtures/test-data/test-audio.mp3';
        if (! file_exists($audioFile)) {
            $audioFile = __DIR__.'/tests/Fixtures/test-data/test-audio.wav';
        }

        if (! file_exists($audioFile)) {
            $this->log('   ⚠️  No audio file found. Please provide:');
            $this->log('      - tests/Fixtures/test-data/test-audio.mp3 OR');
            $this->log('      - tests/Fixtures/test-data/test-audio.wav');
            $this->log('   Audio fixtures will need to be generated manually');

            return;
        }

        $this->log('🎤 Transcribing test audio...');
        if (! $this->dryRun) {
            try {
                $response = $this->mistral->audio()->transcribe(
                    filePath: $audioFile,
                    model: 'voxtral-mini-latest'
                );

                $this->log('   ✅ Audio transcribed successfully');

            } catch (Throwable $e) {
                $this->log('   ⚠️  Failed: '.$e->getMessage());
            }
        }

        // Run audio tests
        $this->log('🧪 Running audio tests to generate fixtures...');
        if (! $this->dryRun) {
            $this->runTests('tests/Resources/AudioTest.php');
        }
    }

    private function runTests(string $testFile): void
    {
        $this->log("   Running: {$testFile}");
        exec("vendor/bin/pest {$testFile} --stop-on-failure 2>&1", $output, $returnCode);

        if ($returnCode === 0) {
            $this->log('   ✅ Tests passed - fixtures generated');
        } else {
            $this->log('   ⚠️  Some tests failed - partial fixtures generated');
        }
    }

    private function printSummary(): void
    {
        $this->log("\n📊 Summary:");
        $this->log('   Created Resources: '.count($this->createdResources));

        if (! empty($this->resourceIds)) {
            $this->log("\n📝 Resource IDs (for reference):");
            foreach ($this->resourceIds as $key => $id) {
                $this->log("   - {$key}: {$id}");
            }
        }

        $this->log("\n📁 Fixtures Location: tests/Fixtures/Saloon/");

        $fixtureCount = $this->countFixtures();
        $this->log("\n   Total Fixtures: {$fixtureCount} files");

        $this->log("\n🧹 Cleanup:");
        $this->log('   To delete created resources, run: php cleanup_resources.php');
    }

    private function countFixtures(): int
    {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__.'/tests/Fixtures/Saloon')
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'json') {
                $count++;
            }
        }

        return $count;
    }

    private function saveResourceIds(): void
    {
        $data = [
            'generated_at' => date('Y-m-d H:i:s'),
            'resources' => $this->createdResources,
            'resource_ids' => $this->resourceIds,
        ];

        file_put_contents(
            __DIR__.'/created_resources.json',
            json_encode($data, JSON_PRETTY_PRINT)
        );

        $this->log("\n💾 Resource IDs saved to: created_resources.json");
    }

    private function log(string $message): void
    {
        echo $message."\n";
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    // Try to load from .env file
    $envFile = __DIR__.'/.env';
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        if (preg_match('/MISTRAL_API_KEY="?([^"\n]+)"?/', $envContent, $matches)) {
            $_ENV['MISTRAL_API_KEY'] = trim($matches[1], '"');
            putenv('MISTRAL_API_KEY='.$_ENV['MISTRAL_API_KEY']);
        }
    }

    $apiKey = getenv('MISTRAL_API_KEY') ?: $_ENV['MISTRAL_API_KEY'] ?? null;
    if (empty($apiKey)) {
        echo "❌ Error: MISTRAL_API_KEY not found\n";
        echo "   Set it in your .env file or export it:\n";
        echo "   export MISTRAL_API_KEY='your-api-key'\n";
        exit(1);
    }

    $dryRun = in_array('--dry-run', $argv ?? []);

    $generator = new FixtureGenerator($apiKey, $dryRun);
    $generator->generate();
}
