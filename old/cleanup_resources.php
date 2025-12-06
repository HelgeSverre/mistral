<?php

/**
 * Cleanup Script for Generated Resources
 *
 * This script deletes resources created by generate_fixtures.php
 *
 * Usage: php cleanup_resources.php
 */

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use HelgeSverre\Mistral\Mistral;

class ResourceCleaner
{
    private Mistral $mistral;

    private array $resources = [];

    public function __construct(string $apiKey)
    {
        $this->mistral = new Mistral(apiKey: $apiKey);
        $this->loadResources();
    }

    public function clean(): void
    {
        echo "🧹 Cleaning up generated resources...\n";
        echo str_repeat('=', 70)."\n\n";

        if (empty($this->resources)) {
            echo "ℹ️  No resources to clean (created_resources.json not found or empty)\n";

            return;
        }

        foreach ($this->resources as $resource) {
            $this->deleteResource($resource);
        }

        echo "\n".str_repeat('=', 70)."\n";
        echo "✅ Cleanup complete!\n";

        // Delete the tracking file
        if (file_exists(__DIR__.'/created_resources.json')) {
            unlink(__DIR__.'/created_resources.json');
            echo "🗑️  Deleted created_resources.json\n";
        }
    }

    private function loadResources(): void
    {
        $file = __DIR__.'/created_resources.json';
        if (! file_exists($file)) {
            return;
        }

        $data = json_decode(file_get_contents($file), true);
        $this->resources = $data['resources'] ?? [];
    }

    private function deleteResource(array $resource): void
    {
        $type = $resource['type'];
        $id = $resource['id'];

        echo "Deleting {$type}: {$id}... ";

        try {
            switch ($type) {
                case 'agent':
                    // Note: Agents may not have a delete endpoint
                    echo "⚠️  Skipped (no delete API available)\n";
                    break;

                case 'conversation':
                    // Note: Conversations may not have a delete endpoint
                    echo "⚠️  Skipped (no delete API available)\n";
                    break;

                case 'batch_job':
                    // Cancel batch job
                    $this->mistral->batch()->cancel($id);
                    echo "✅ Cancelled\n";
                    break;

                case 'file':
                    $this->mistral->files()->delete($id);
                    echo "✅ Deleted\n";
                    break;

                case 'fine_tune_job':
                    $this->mistral->fineTuning()->cancel($id);
                    echo "✅ Cancelled\n";
                    break;

                default:
                    echo "⚠️  Unknown type\n";
            }
        } catch (Throwable $e) {
            echo '❌ Failed: '.$e->getMessage()."\n";
        }
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $apiKey = getenv('MISTRAL_API_KEY');
    if (empty($apiKey)) {
        echo "❌ Error: MISTRAL_API_KEY environment variable not set\n";
        exit(1);
    }

    $cleaner = new ResourceCleaner($apiKey);
    $cleaner->clean();
}
