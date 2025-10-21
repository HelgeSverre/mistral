<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class TrainingFile extends Data
{
    public function __construct(
        #[MapName('file_id')]
        public string $fileId,
        public ?int $weight = null,
    ) {}
}
