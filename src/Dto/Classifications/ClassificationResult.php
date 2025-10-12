<?php

namespace HelgeSverre\Mistral\Dto\Classifications;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ClassificationResult extends SpatieData
{
    public function __construct(
        #[MapName('predicted_class')]
        public string $predictedClass,
        public array $categories,
        #[MapName('category_scores')]
        public array $categoryScores,
    ) {}
}
