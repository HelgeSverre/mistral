<?php

namespace HelgeSverre\Mistral\Dto\Files;

use HelgeSverre\Mistral\Enums\FilePurpose;
use HelgeSverre\Mistral\Enums\SampleType;
use HelgeSverre\Mistral\Enums\Source;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class ListFilesRequest extends Data
{
    public function __construct(
        public ?int $page = null,
        #[MapName('page_size')]
        public ?int $pageSize = null,
        #[MapName('sample_type')]
        public ?SampleType $sampleType = null,
        public ?Source $source = null,
        public ?string $search = null,
        public ?FilePurpose $purpose = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'page' => $this->page,
            'page_size' => $this->pageSize,
            'sample_type' => $this->sampleType?->value,
            'source' => $this->source?->value,
            'search' => $this->search,
            'purpose' => $this->purpose?->value,
        ], fn ($value) => $value !== null);
    }
}
