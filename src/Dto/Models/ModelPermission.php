<?php

namespace HelgeSverre\Mistral\Dto\Models;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

/**
 * Model Permissions
 */
class ModelPermission extends SpatieData
{
    public function __construct(

        public ?string $id,
        public ?string $object,
        public ?int $created,

        #[MapName('allow_create_engine')]
        public bool $allowCreateEngine,

        #[MapName('allow_sampling')]
        public bool $allowSampling,

        #[MapName('allow_logprobs')]
        public bool $allowLogProbs,

        #[MapName('allow_search_indices')]
        public bool $allowSearchIndices,

        #[MapName('allow_view')]
        public bool $allowView,

        #[MapName('allow_fine_tuning')]
        public bool $allowFineTuning,

        public ?string $organization,
        public ?string $group,
        #[MapName('is_blocking')]
        public bool $isBlocking,
    ) {
    }
}
