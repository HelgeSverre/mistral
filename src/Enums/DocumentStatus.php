<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Enums;

enum DocumentStatus: string
{
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case PROCESSED = 'processed';
    case FAILED = 'failed';
}
