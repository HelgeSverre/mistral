<?php

namespace HelgeSverre\Mistral\Dto\Batch;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class BatchJobIn extends Data
{
    /**
     * @param  string[]  $inputFiles  Array of file UUIDs
     * @param  string  $endpoint  API endpoint (e.g. /v1/chat/completions)
     * @param  array<string,string>|null  $metadata  Key-value metadata (max 16 pairs, 64 char keys, 512 char values)
     * @param  int|null  $timeoutHours  Timeout in hours (default: 24)
     */
    public function __construct(
        #[MapName('input_files')]
        public array $inputFiles,
        public string $endpoint,
        public ?string $model = null,
        #[MapName('agent_id')]
        public ?string $agentId = null,
        public ?array $metadata = null,
        #[MapName('timeout_hours')]
        public ?int $timeoutHours = null,
    ) {}
}
