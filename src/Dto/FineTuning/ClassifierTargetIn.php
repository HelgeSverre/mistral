<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Data;

class ClassifierTargetIn extends Data
{
    /**
     * @param  string[]  $labels  The labels for this target
     */
    public function __construct(
        public string $name,
        public array $labels,
    ) {}
}
