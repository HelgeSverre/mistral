<?php

namespace HelgeSverre\Mistral\Dto\Files;

use HelgeSverre\Mistral\Enums\FilePurpose;
use HelgeSverre\Mistral\Enums\SampleType;
use HelgeSverre\Mistral\Enums\Source;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class ListFilesRequest extends Data
{
    /**
     * @param  SampleType[]|null  $sampleTypes  Filter by sample types
     * @param  Source[]|null  $sources  Filter by sources
     */
    public function __construct(
        public ?int $page = null,
        #[MapName('page_size')]
        public ?int $pageSize = null,
        #[MapName('sample_type')]
        public ?array $sampleTypes = null,
        public ?array $sources = null,
        public ?string $search = null,
        public ?FilePurpose $purpose = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'page' => $this->page,
            'page_size' => $this->pageSize,
            'sample_type' => $this->sampleTypes !== null
                ? array_map(fn (SampleType $type) => $type->value, $this->sampleTypes)
                : null,
            'source' => $this->sources !== null
                ? array_map(fn (Source $source) => $source->value, $this->sources)
                : null,
            'search' => $this->search,
            'purpose' => $this->purpose?->value,
        ], fn ($value) => $value !== null);
    }
}
