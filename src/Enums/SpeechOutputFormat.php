<?php

namespace HelgeSverre\Mistral\Enums;

enum SpeechOutputFormat: string
{
    case PCM = 'pcm';
    case WAV = 'wav';
    case MP3 = 'mp3';
    case FLAC = 'flac';
    case OPUS = 'opus';
}
