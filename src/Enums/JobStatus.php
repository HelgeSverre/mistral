<?php

namespace HelgeSverre\Mistral\Enums;

enum JobStatus: string
{
    case QUEUED = 'QUEUED';
    case STARTED = 'STARTED';
    case VALIDATING = 'VALIDATING';
    case VALIDATED = 'VALIDATED';
    case RUNNING = 'RUNNING';
    case FAILED_VALIDATION = 'FAILED_VALIDATION';
    case FAILED = 'FAILED';
    case SUCCESS = 'SUCCESS';
    case CANCELLED = 'CANCELLED';
    case CANCELLATION_REQUESTED = 'CANCELLATION_REQUESTED';
}
