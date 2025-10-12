<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Concerns\HandlesStreamedResponses;
use HelgeSverre\Mistral\Dto\Fim\FIMCompletionRequest;
use HelgeSverre\Mistral\Dto\Fim\StreamedFIMCompletionResponse;
use HelgeSverre\Mistral\Requests\Fim\CreateFIMCompletionRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Fim extends BaseResource
{
    use HandlesStreamedResponses;

    public function create(
        string $model,
        string $prompt,
        ?string $suffix = null,
        float $temperature = 0.7,
        ?int $maxTokens = null,
        ?float $topP = null,
        ?int $minTokens = null,
        ?int $randomSeed = null,
        string|array|null $stop = null,
    ): Response {
        return $this->connector->send(new CreateFIMCompletionRequest(
            new FIMCompletionRequest(
                model: $model,
                prompt: $prompt,
                suffix: $suffix,
                temperature: $temperature,
                topP: $topP,
                maxTokens: $maxTokens,
                minTokens: $minTokens,
                stream: false,
                randomSeed: $randomSeed,
                stop: $stop,
            )
        ));
    }

    /**
     * @return Generator|StreamedFIMCompletionResponse[]
     *
     * @noinspection PhpDocSignatureInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function createStreamed(
        string $model,
        string $prompt,
        ?string $suffix = null,
        float $temperature = 0.7,
        ?int $maxTokens = null,
        ?float $topP = null,
        ?int $minTokens = null,
        ?int $randomSeed = null,
        string|array|null $stop = null,
    ): Generator {
        $response = $this->connector->send(new CreateFIMCompletionRequest(
            new FIMCompletionRequest(
                model: $model,
                prompt: $prompt,
                suffix: $suffix,
                temperature: $temperature,
                topP: $topP,
                maxTokens: $maxTokens,
                minTokens: $minTokens,
                stream: true,
                randomSeed: $randomSeed,
                stop: $stop,
            )
        ));

        foreach ($this->getStreamIterator($response->stream()) as $fimResponse) {
            yield StreamedFIMCompletionResponse::from($fimResponse);
        }
    }
}
