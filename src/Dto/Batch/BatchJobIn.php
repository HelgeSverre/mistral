<?php

namespace HelgeSverre\Mistral\Dto\Batch;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class BatchJobIn extends Data
{
    /**
     * @param  string[]  $inputFiles
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
        #[MapName('completion_window')]
        public ?string $completionWindow = null,
    ) {}
}
