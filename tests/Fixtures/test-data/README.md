# Test Data Files

This directory contains test data files used for fixture generation.

## Files

### training-data.jsonl
Fine-tuning training data in JSONL format. Contains sample Q&A pairs for model training.

### batch-input.jsonl
Batch job input file. Contains multiple API requests to be processed in batch mode.

### Audio File (REQUIRED)
**You need to provide a real audio file:**
- **Filename**: `test-audio.mp3` or `test-audio.wav`
- **Duration**: 10-30 seconds
- **Content**: Clear speech in English
- **Format**: MP3 or WAV
- **Purpose**: Testing audio transcription with `voxtral-mini-latest` model

You can:
1. Record a short audio clip saying "Hello, this is a test audio file for the Mistral PHP SDK"
2. Download a sample audio file
3. Use any existing audio file with clear speech

### Document File (OPTIONAL)
For OCR testing, you can provide:
- **Filename**: `test-document.pdf` or `test-image.png`
- **Content**: Simple text document or image with text
- **Purpose**: Testing OCR with `mistral-ocr-latest` model

## Usage

These files are used by `generate_fixtures.php` to create real API resources and generate test fixtures.
