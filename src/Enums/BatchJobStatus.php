<?php

namespace HelgeSverre\Mistral\Enums;

enum BatchJobStatus: string
{
    case QUEUED = 'QUEUED';
    case RUNNING = 'RUNNING';
    case SUCCESS = 'SUCCESS';
    case FAILED = 'FAILED';
    case CANCELLED = 'CANCELLED';
    case CANCELLATION_REQUESTED = 'CANCELLATION_REQUESTED';
}
