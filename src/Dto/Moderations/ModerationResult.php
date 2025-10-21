<?php

namespace HelgeSverre\Mistral\Dto\Moderations;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data as SpatieData;

class ModerationResult extends SpatieData
{
    public function __construct(
        public ModerationCategories $categories,
        #[MapName('category_scores')]
        public ModerationCategoryScores $categoryScores,
        public ?bool $flagged = null,
    ) {}

    /**
     * Check if content is flagged by moderation.
     * If the flagged field is null, computes it from categories.
     *
     * Note: Use this method instead of accessing $flagged directly,
     * as the API may not always return the flagged field.
     */
    public function isFlagged(): bool
    {
        if ($this->flagged !== null) {
            return $this->flagged;
        }

        // Content is flagged if any category is true
        return $this->categories->sexual
            || $this->categories->hateAndDiscrimination
            || $this->categories->violenceAndThreats
            || $this->categories->dangerousAndCriminalContent
            || $this->categories->selfharm
            || $this->categories->health
            || $this->categories->financial
            || $this->categories->law
            || $this->categories->pii;
    }
}
