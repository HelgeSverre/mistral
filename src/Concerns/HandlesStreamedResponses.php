<?php

namespace HelgeSverre\Mistral\Concerns;

// Credit: https://github.com/openai-php/client/blob/main/src/Responses/StreamResponse.php
use Generator;
use Psr\Http\Message\StreamInterface;

trait HandlesStreamedResponses
{
    protected function getStreamIterator(StreamInterface $stream): Generator
    {
        while (! $stream->eof()) {
            $line = $this->readLine($stream);

            if (! str_starts_with($line, 'data:')) {
                continue;
            }

            $data = trim(substr($line, strlen('data:')));

            if ($data === '[DONE]') {
                break;
            }

            $response = json_decode($data, true, flags: JSON_THROW_ON_ERROR);

            yield $response;
        }
    }

    protected function readLine($stream): string
    {
        $buffer = '';
        while (! $stream->eof()) {
            if ('' === ($byte = $stream->read(1))) {
                return $buffer;
            }
            $buffer .= $byte;
            if ($byte === "\n") {
                break;
            }
        }

        return $buffer;
    }
}
