<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Enums;

enum DocumentStatus: string
{
    case QUEUED = 'Queued';
    case PROCESSING = 'Processing';
    case COMPLETED = 'Completed';
    case FAILED = 'Failed';

    // Backwards compatibility alias (deprecated) - use COMPLETED instead
    case PROCESSED = 'Processed';
}
