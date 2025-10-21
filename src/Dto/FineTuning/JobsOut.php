<?php

namespace HelgeSverre\Mistral\Dto\FineTuning;

use Spatie\LaravelData\Data;

class JobsOut extends Data
{
    /**
     * @param  array<CompletionJobOut|ClassifierJobOut>  $data
     */
    public function __construct(
        public array $data,
        public string $object = 'list',
        public ?int $total = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $jobs = [];
        foreach ($data['data'] as $jobData) {
            $jobs[] = match ($jobData['job_type'] ?? '') {
                'completion' => CompletionJobOut::from($jobData),
                'classifier' => ClassifierJobOut::from($jobData),
                default => CompletionJobOut::from($jobData),
            };
        }

        return new self(
            data: $jobs,
            object: $data['object'] ?? 'list',
            total: $data['total'] ?? null,
        );
    }
}
