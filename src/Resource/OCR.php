<?php

namespace HelgeSverre\Mistral\Resource;

use HelgeSverre\Mistral\Dto\OCR\Document;
use HelgeSverre\Mistral\Dto\OCR\OCRRequest;
use HelgeSverre\Mistral\Requests\OCR\ProcessDocument;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class OCR extends BaseResource
{
    /**
     * Process a document with OCR
     *
     * @param  string  $model  The OCR model to use
     * @param  Document|string  $document  The document to process (Document object, URL, or base64 encoded string)
     * @param  string|null  $mimeType  The MIME type when passing base64 encoded data
     * @param  bool|null  $includeImageBase64  Whether to include base64 encoded images in the response
     */
    public function process(
        string $model = 'mistral-ocr-latest',
        Document|string|null $document = null,
        ?string $mimeType = null,
        ?bool $includeImageBase64 = null,
    ): Response {
        // Handle different document input types
        if (is_string($document)) {
            // Check if it's a URL or base64
            if (filter_var($document, FILTER_VALIDATE_URL)) {
                $documentDto = Document::fromUrl($document);
            } else {
                // Assume it's base64 encoded
                if ($mimeType === null) {
                    throw new \InvalidArgumentException('MIME type is required when passing base64 encoded data');
                }
                $documentDto = Document::fromBase64($document, $mimeType);
            }
        } else {
            $documentDto = $document;
        }

        return $this->connector->send(new ProcessDocument(
            new OCRRequest(
                model: $model,
                document: $documentDto,
                includeImageBase64: $includeImageBase64,
            )
        ));
    }

    /**
     * Process a document from URL
     *
     * @param  string  $url  The URL of the document to process
     * @param  string  $model  The OCR model to use
     * @param  bool|null  $includeImageBase64  Whether to include base64 encoded images in the response
     */
    public function processUrl(
        string $url,
        string $model = 'mistral-ocr-latest',
        ?bool $includeImageBase64 = null,
    ): Response {
        return $this->process(
            model: $model,
            document: Document::fromUrl($url),
            includeImageBase64: $includeImageBase64,
        );
    }

    /**
     * Process a document from base64 encoded data
     *
     * @param  string  $base64  The base64 encoded document
     * @param  string  $mimeType  The MIME type of the document
     * @param  string  $model  The OCR model to use
     * @param  bool|null  $includeImageBase64  Whether to include base64 encoded images in the response
     */
    public function processBase64(
        string $base64,
        string $mimeType,
        string $model = 'mistral-ocr-latest',
        ?bool $includeImageBase64 = null,
    ): Response {
        return $this->process(
            model: $model,
            document: Document::fromBase64($base64, $mimeType),
            includeImageBase64: $includeImageBase64,
        );
    }
}
