<?php

namespace HelgeSverre\Mistral\Dto\OCR;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class Document extends Data
{
    public function __construct(
        public string $type,
        #[MapName('document_url')]
        public ?string $documentUrl = null,
        #[MapName('image_url')]
        public ?string $imageUrl = null,
        #[MapName('file_id')]
        public ?string $fileId = null,
    ) {}

    public function toArray(): array
    {
        $data = ['type' => $this->type];

        // Only include the field that matches the type
        switch ($this->type) {
            case 'document_url':
                if ($this->documentUrl !== null) {
                    $data['document_url'] = $this->documentUrl;
                }
                break;
            case 'image_url':
                if ($this->imageUrl !== null) {
                    // Based on the error, image_url expects an object with a url field
                    $data['image_url'] = [
                        'url' => $this->imageUrl,
                    ];
                }
                break;
            case 'file':
                if ($this->fileId !== null) {
                    $data['file_id'] = $this->fileId;
                }
                break;
        }

        return $data;
    }

    public static function fromDocumentUrl(string $url): self
    {
        return new self(
            type: 'document_url',
            documentUrl: $url,
        );
    }

    public static function fromImageUrl(string $url): self
    {
        return new self(
            type: 'image_url',
            imageUrl: $url,
        );
    }

    public static function fromUrl(string $url): self
    {
        // Default to document_url for backward compatibility
        return self::fromDocumentUrl($url);
    }

    public static function fromBase64(string $base64, string $mimeType): self
    {
        // Determine if it's an image or document based on MIME type
        $imageTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/avif'];

        if (in_array($mimeType, $imageTypes)) {
            return new self(
                type: 'image_url',
                imageUrl: "data:{$mimeType};base64,{$base64}",
            );
        }

        return new self(
            type: 'document_url',
            documentUrl: "data:{$mimeType};base64,{$base64}",
        );
    }

    public static function fromFileId(string $fileId): self
    {
        return new self(
            type: 'file',
            fileId: $fileId,
        );
    }
}
