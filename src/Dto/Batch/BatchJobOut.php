<?php

namespace HelgeSverre\Mistral\Dto\Batch;

use HelgeSverre\Mistral\Enums\BatchJobStatus;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class BatchJobOut extends Data
{
    /**
     * @param  string[]  $inputFiles
     */
    public function __construct(
        public string $id,
        public string $object,
        public BatchJobStatus $status,
        #[MapName('input_files')]
        public array $inputFiles,
        public string $endpoint,
        #[MapName('created_at')]
        public int $createdAt,
        #[MapName('started_at')]
        public ?int $startedAt = null,
        #[MapName('completed_at')]
        public ?int $completedAt = null,
        public ?string $model = null,
        #[MapName('agent_id')]
        public ?string $agentId = null,
        public ?array $metadata = null,
        #[MapName('output_file')]
        public ?string $outputFile = null,
        #[MapName('error_file')]
        public ?string $errorFile = null,
        #[MapName('total_requests')]
        public ?int $totalRequests = null,
        #[MapName('succeeded_requests')]
        public ?int $succeededRequests = null,
        #[MapName('failed_requests')]
        public ?int $failedRequests = null,
    ) {}
}
