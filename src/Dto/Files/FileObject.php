<?php

namespace HelgeSverre\Mistral\Dto\Files;

use HelgeSverre\Mistral\Enums\FilePurpose;
use HelgeSverre\Mistral\Enums\SampleType;
use HelgeSverre\Mistral\Enums\Source;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class FileObject extends Data
{
    public function __construct(
        public string $id,
        public string $object,
        public int $bytes,
        #[MapName('created_at')]
        public int $createdAt,
        public string $filename,
        public ?FilePurpose $purpose = null,
        #[MapName('sample_type')]
        public ?SampleType $sampleType = null,
        #[MapName('num_lines')]
        public ?int $numLines = null,
        public ?Source $source = null,
        public ?bool $deleted = null,
    ) {}
}
