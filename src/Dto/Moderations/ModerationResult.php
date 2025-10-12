<?php

namespace HelgeSverre\Mistral\Dto\Moderations;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ModerationResult extends SpatieData
{
    public function __construct(
        public bool $flagged,
        public ModerationCategories $categories,
        #[MapName('category_scores')]
        public ModerationCategoryScores $categoryScores,
    ) {}
}
