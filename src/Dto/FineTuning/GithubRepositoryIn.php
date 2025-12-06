<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class GithubRepositoryIn extends Data
{
    public function __construct(
        public string $name,
        public string $owner,
        public ?string $ref = null,
        public ?float $weight = null,
        #[MapName('commit_id')]
        public ?string $commitId = null,
    ) {}
}
