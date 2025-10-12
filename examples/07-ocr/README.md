# OCR Document Processing with Mistral PHP SDK

## Overview

This example demonstrates Mistral's Optical Character Recognition (OCR) capabilities for extracting and processing text
from images and documents. The Pixtral model combines vision and language understanding, enabling you to extract text,
analyze document structure, and answer questions about visual content.

### Real-world Use Cases

- Invoice and receipt processing
- Form data extraction
- ID card and passport scanning
- Handwritten text digitization
- Table and chart analysis
- Multi-language document processing

### Prerequisites

- Completed [06-embeddings](../06-embeddings) example
- Understanding of image formats (JPEG, PNG, PDF)
- Basic knowledge of document processing
- Familiarity with base64 encoding

## Concepts

### Pixtral Vision-Language Model

Mistral's Pixtral is a multimodal model that can:

- **Extract Text**: OCR from images and PDFs
- **Understand Layout**: Recognize document structure
- **Answer Questions**: Respond to queries about visual content
- **Multiple Languages**: Process documents in various languages

### Document Types Supported

- **Images**: JPEG, PNG, WebP formats
- **PDFs**: Single and multi-page documents
- **Screenshots**: UI and application captures
- **Handwriting**: Handwritten notes and forms
- **Mixed Content**: Documents with text, tables, and graphics

### Processing Modes

- **Text Extraction**: Pure OCR to get all text
- **Structured Extraction**: Parse forms, tables, and layouts
- **Visual Q&A**: Ask questions about document content
- **Translation**: Extract and translate in one step

## Implementation

### Basic OCR Text Extraction

Extract text from an image:

```php
<?php

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Dto\Chat\ImageContent;
use Helge\Mistral\Dto\Chat\TextContent;
use Helge\Mistral\Enums\Role;

$mistral = new Mistral($_ENV['MISTRAL_API_KEY']);

// Load and encode image
$imagePath = '/path/to/document.jpg';
$imageData = base64_encode(file_get_contents($imagePath));
$imageUrl = "data:image/jpeg;base64,{$imageData}";

// Create OCR request
$request = ChatCompletionRequest::from([
    'model' => 'pixtral-12b-2409',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => [
                TextContent::from([
                    'type' => 'text',
                    'text' => 'Extract all text from this image.',
                ]),
                ImageContent::from([
                    'type' => 'image_url',
                    'imageUrl' => ['url' => $imageUrl],
                ]),
            ],
        ]),
    ],
    'temperature' => 0.0, // Use low temperature for accuracy
]);

$response = $mistral->chat()->create($request);
$extractedText = $response->choices[0]->message->content;

echo "Extracted text:\n{$extractedText}\n";
```

### Advanced Document Processing

Build a comprehensive document processor:

```php
class DocumentProcessor
{
    private Mistral $client;
    private string $model = 'pixtral-12b-2409';

    public function __construct(string $apiKey)
    {
        $this->client = new Mistral($apiKey);
    }

    public function processDocument(string $filePath, array $options = []): array
    {
        $fileInfo = pathinfo($filePath);
        $mimeType = $this->getMimeType($fileInfo['extension']);

        // Read and encode file
        $content = file_get_contents($filePath);
        $base64 = base64_encode($content);
        $dataUrl = "data:{$mimeType};base64,{$base64}";

        $results = [];

        // Extract text
        if ($options['extractText'] ?? true) {
            $results['text'] = $this->extractText($dataUrl);
        }

        // Extract structured data
        if ($options['extractStructure'] ?? false) {
            $results['structure'] = $this->extractStructure($dataUrl);
        }

        // Extract tables
        if ($options['extractTables'] ?? false) {
            $results['tables'] = $this->extractTables($dataUrl);
        }

        // Answer specific questions
        if (!empty($options['questions'])) {
            $results['answers'] = $this->answerQuestions(
                $dataUrl,
                $options['questions']
            );
        }

        return $results;
    }

    private function extractText(string $imageUrl): string
    {
        $request = ChatCompletionRequest::from([
            'model' => $this->model,
            'messages' => [
                ChatMessage::from([
                    'role' => Role::User,
                    'content' => [
                        TextContent::from([
                            'type' => 'text',
                            'text' => 'Extract all text from this document.
                                      Preserve the original formatting and structure.',
                        ]),
                        ImageContent::from([
                            'type' => 'image_url',
                            'imageUrl' => ['url' => $imageUrl],
                        ]),
                    ],
                ]),
            ],
            'temperature' => 0.0,
        ]);

        $response = $this->client->chat()->create($request);
        return $response->choices[0]->message->content;
    }

    private function extractStructure(string $imageUrl): array
    {
        $request = ChatCompletionRequest::from([
            'model' => $this->model,
            'messages' => [
                ChatMessage::from([
                    'role' => Role::System,
                    'content' => 'You are a document structure analyzer.
                                 Return results as JSON.',
                ]),
                ChatMessage::from([
                    'role' => Role::User,
                    'content' => [
                        TextContent::from([
                            'type' => 'text',
                            'text' => 'Analyze this document and return its structure as JSON:
                                      - document_type (invoice, form, letter, etc.)
                                      - sections (array of section names)
                                      - has_tables (boolean)
                                      - has_images (boolean)
                                      - language
                                      - key_value_pairs (if any)',
                        ]),
                        ImageContent::from([
                            'type' => 'image_url',
                            'imageUrl' => ['url' => $imageUrl],
                        ]),
                    ],
                ]),
            ],
            'temperature' => 0.0,
            'responseFormat' => ['type' => 'json_object'],
        ]);

        $response = $this->client->chat()->create($request);
        return json_decode($response->choices[0]->message->content, true);
    }

    private function extractTables(string $imageUrl): array
    {
        $request = ChatCompletionRequest::from([
            'model' => $this->model,
            'messages' => [
                ChatMessage::from([
                    'role' => Role::User,
                    'content' => [
                        TextContent::from([
                            'type' => 'text',
                            'text' => 'Extract all tables from this document.
                                      Format each table as a JSON array of objects,
                                      where each object represents a row.',
                        ]),
                        ImageContent::from([
                            'type' => 'image_url',
                            'imageUrl' => ['url' => $imageUrl],
                        ]),
                    ],
                ]),
            ],
            'temperature' => 0.0,
            'responseFormat' => ['type' => 'json_object'],
        ]);

        $response = $this->client->chat()->create($request);
        return json_decode($response->choices[0]->message->content, true);
    }

    private function answerQuestions(string $imageUrl, array $questions): array
    {
        $answers = [];

        foreach ($questions as $question) {
            $request = ChatCompletionRequest::from([
                'model' => $this->model,
                'messages' => [
                    ChatMessage::from([
                        'role' => Role::User,
                        'content' => [
                            TextContent::from([
                                'type' => 'text',
                                'text' => $question,
                            ]),
                            ImageContent::from([
                                'type' => 'image_url',
                                'imageUrl' => ['url' => $imageUrl],
                            ]),
                        ],
                    ]),
                ],
                'temperature' => 0.0,
            ]);

            $response = $this->client->chat()->create($request);
            $answers[$question] = $response->choices[0]->message->content;
        }

        return $answers;
    }

    private function getMimeType(string $extension): string
    {
        return match(strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
```

### Form Data Extraction

Extract specific fields from forms:

```php
class FormExtractor
{
    private Mistral $client;

    public function extractFormData(string $imagePath, array $fields): array
    {
        $imageData = base64_encode(file_get_contents($imagePath));
        $imageUrl = "data:image/jpeg;base64,{$imageData}";

        $fieldsList = implode("\n", array_map(fn($f) => "- {$f}", $fields));

        $request = ChatCompletionRequest::from([
            'model' => 'pixtral-12b-2409',
            'messages' => [
                ChatMessage::from([
                    'role' => Role::System,
                    'content' => 'Extract form fields and return as JSON.
                                 For missing fields, use null.',
                ]),
                ChatMessage::from([
                    'role' => Role::User,
                    'content' => [
                        TextContent::from([
                            'type' => 'text',
                            'text' => "Extract these fields from the form:\n{$fieldsList}",
                        ]),
                        ImageContent::from([
                            'type' => 'image_url',
                            'imageUrl' => ['url' => $imageUrl],
                        ]),
                    ],
                ]),
            ],
            'temperature' => 0.0,
            'responseFormat' => ['type' => 'json_object'],
        ]);

        $response = $this->client->chat()->create($request);
        return json_decode($response->choices[0]->message->content, true);
    }
}
```

## Code Example

Complete working example (`ocr.php`):

```php
<?php

require_once 'vendor/autoload.php';

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Dto\Chat\ImageContent;
use Helge\Mistral\Dto\Chat\TextContent;
use Helge\Mistral\Enums\Role;

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');
if (!$apiKey) {
    die("Please set your MISTRAL_API_KEY environment variable\n");
}

$mistral = new Mistral($apiKey);

// Example 1: Basic Text Extraction
echo "=== Example 1: Basic OCR ===\n\n";

// Create a sample image with text (for demo purposes)
$sampleImage = createSampleDocument();
$imageData = base64_encode($sampleImage);
$imageUrl = "data:image/png;base64,{$imageData}";

$request = ChatCompletionRequest::from([
    'model' => 'pixtral-12b-2409',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => [
                TextContent::from([
                    'type' => 'text',
                    'text' => 'Extract all text from this image.',
                ]),
                ImageContent::from([
                    'type' => 'image_url',
                    'imageUrl' => ['url' => $imageUrl],
                ]),
            ],
        ]),
    ],
    'temperature' => 0.0,
]);

$response = $mistral->chat()->create($request);
echo "Extracted Text:\n";
echo $response->choices[0]->message->content . "\n\n";

// Example 2: Invoice Processing
echo "=== Example 2: Invoice Analysis ===\n\n";

// For demo, we'll use the same sample image
$request = ChatCompletionRequest::from([
    'model' => 'pixtral-12b-2409',
    'messages' => [
        ChatMessage::from([
            'role' => Role::System,
            'content' => 'You are an invoice processing system.
                         Extract key information and return as JSON.',
        ]),
        ChatMessage::from([
            'role' => Role::User,
            'content' => [
                TextContent::from([
                    'type' => 'text',
                    'text' => 'Extract the following from this invoice:
                             - invoice_number
                             - date
                             - total_amount
                             - vendor_name
                             - line_items (array of items with description and amount)',
                ]),
                ImageContent::from([
                    'type' => 'image_url',
                    'imageUrl' => ['url' => $imageUrl],
                ]),
            ],
        ]),
    ],
    'temperature' => 0.0,
    'responseFormat' => ['type' => 'json_object'],
]);

$response = $mistral->chat()->create($request);
$invoiceData = json_decode($response->choices[0]->message->content, true);

echo "Invoice Data:\n";
echo json_encode($invoiceData, JSON_PRETTY_PRINT) . "\n\n";

// Example 3: Multi-language OCR
echo "=== Example 3: Multi-language Document ===\n\n";

$multiLangImage = createMultiLanguageDocument();
$imageData = base64_encode($multiLangImage);
$imageUrl = "data:image/png;base64,{$imageData}";

$request = ChatCompletionRequest::from([
    'model' => 'pixtral-12b-2409',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => [
                TextContent::from([
                    'type' => 'text',
                    'text' => 'Extract text from this document.
                             Identify the languages used and preserve them.',
                ]),
                ImageContent::from([
                    'type' => 'image_url',
                    'imageUrl' => ['url' => $imageUrl],
                ]),
            ],
        ]),
    ],
    'temperature' => 0.0,
]);

$response = $mistral->chat()->create($request);
echo "Multi-language text:\n";
echo $response->choices[0]->message->content . "\n\n";

// Example 4: Table Extraction
echo "=== Example 4: Table Data Extraction ===\n\n";

$tableImage = createTableDocument();
$imageData = base64_encode($tableImage);
$imageUrl = "data:image/png;base64,{$imageData}";

$request = ChatCompletionRequest::from([
    'model' => 'pixtral-12b-2409',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => [
                TextContent::from([
                    'type' => 'text',
                    'text' => 'Extract the table data from this image.
                             Format as CSV with headers.',
                ]),
                ImageContent::from([
                    'type' => 'image_url',
                    'imageUrl' => ['url' => $imageUrl],
                ]),
            ],
        ]),
    ],
    'temperature' => 0.0,
]);

$response = $mistral->chat()->create($request);
echo "Table Data (CSV):\n";
echo $response->choices[0]->message->content . "\n\n";

// Example 5: Visual Q&A
echo "=== Example 5: Document Q&A ===\n\n";

$questions = [
    "What is the main topic of this document?",
    "Are there any dates mentioned?",
    "What numbers or amounts are shown?",
    "Is this document signed?",
];

echo "Analyzing document with Q&A...\n\n";

foreach ($questions as $question) {
    $request = ChatCompletionRequest::from([
        'model' => 'pixtral-12b-2409',
        'messages' => [
            ChatMessage::from([
                'role' => Role::User,
                'content' => [
                    TextContent::from([
                        'type' => 'text',
                        'text' => $question,
                    ]),
                    ImageContent::from([
                        'type' => 'image_url',
                        'imageUrl' => ['url' => $imageUrl],
                    ]),
                ],
            ]),
        ],
        'temperature' => 0.0,
        'maxTokens' => 100,
    ]);

    $response = $mistral->chat()->create($request);
    echo "Q: {$question}\n";
    echo "A: " . $response->choices[0]->message->content . "\n\n";
}

// Example 6: Handwriting Recognition
echo "=== Example 6: Handwriting OCR ===\n\n";

$handwrittenImage = createHandwrittenNote();
$imageData = base64_encode($handwrittenImage);
$imageUrl = "data:image/png;base64,{$imageData}";

$request = ChatCompletionRequest::from([
    'model' => 'pixtral-12b-2409',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => [
                TextContent::from([
                    'type' => 'text',
                    'text' => 'This image contains handwritten text.
                             Please extract and transcribe it carefully.',
                ]),
                ImageContent::from([
                    'type' => 'image_url',
                    'imageUrl' => ['url' => $imageUrl],
                ]),
            ],
        ]),
    ],
    'temperature' => 0.0,
]);

$response = $mistral->chat()->create($request);
echo "Handwritten text:\n";
echo $response->choices[0]->message->content . "\n\n";

// Helper functions to create sample documents
function createSampleDocument(): string
{
    $image = imagecreate(800, 600);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    imagefill($image, 0, 0, $white);

    // Add sample text
    $font = 5;
    imagestring($image, $font, 50, 50, "INVOICE #2024-001", $black);
    imagestring($image, $font, 50, 100, "Date: January 15, 2024", $black);
    imagestring($image, $font, 50, 150, "Bill To: Acme Corporation", $black);
    imagestring($image, $font, 50, 200, "Item: Professional Services", $black);
    imagestring($image, $font, 50, 250, "Amount: $1,500.00", $black);
    imagestring($image, $font, 50, 300, "Total Due: $1,500.00", $black);

    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    return $imageData;
}

function createMultiLanguageDocument(): string
{
    $image = imagecreate(800, 400);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    imagefill($image, 0, 0, $white);

    $font = 5;
    imagestring($image, $font, 50, 50, "English: Hello World", $black);
    imagestring($image, $font, 50, 100, "French: Bonjour le monde", $black);
    imagestring($image, $font, 50, 150, "Spanish: Hola mundo", $black);
    imagestring($image, $font, 50, 200, "German: Hallo Welt", $black);

    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    return $imageData;
}

function createTableDocument(): string
{
    $image = imagecreate(600, 300);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 200, 200, 200);

    imagefill($image, 0, 0, $white);

    // Draw table
    $rows = 4;
    $cols = 3;
    $cellWidth = 150;
    $cellHeight = 50;
    $startX = 50;
    $startY = 50;

    // Headers
    $headers = ['Product', 'Quantity', 'Price'];
    $data = [
        ['Widget A', '10', '$25.00'],
        ['Widget B', '5', '$40.00'],
        ['Widget C', '15', '$15.00'],
    ];

    // Draw grid
    for ($i = 0; $i <= $rows; $i++) {
        imageline($image, $startX, $startY + $i * $cellHeight,
                  $startX + $cols * $cellWidth, $startY + $i * $cellHeight, $black);
    }
    for ($j = 0; $j <= $cols; $j++) {
        imageline($image, $startX + $j * $cellWidth, $startY,
                  $startX + $j * $cellWidth, $startY + $rows * $cellHeight, $black);
    }

    // Add headers
    $font = 5;
    for ($j = 0; $j < $cols; $j++) {
        imagestring($image, $font,
                    $startX + $j * $cellWidth + 10,
                    $startY + 15,
                    $headers[$j], $black);
    }

    // Add data
    for ($i = 0; $i < count($data); $i++) {
        for ($j = 0; $j < $cols; $j++) {
            imagestring($image, $font,
                        $startX + $j * $cellWidth + 10,
                        $startY + ($i + 1) * $cellHeight + 15,
                        $data[$i][$j], $black);
        }
    }

    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    return $imageData;
}

function createHandwrittenNote(): string
{
    $image = imagecreate(600, 200);
    $white = imagecolorallocate($image, 255, 255, 255);
    $blue = imagecolorallocate($image, 0, 0, 150);

    imagefill($image, 0, 0, $white);

    // Simulate handwriting with varied positioning
    $font = 4;
    imagestring($image, $font, 50, 50, "Meeting Notes - Jan 15", $blue);
    imagestring($image, $font, 55, 80, "- Discuss Q1 targets", $blue);
    imagestring($image, $font, 52, 110, "- Review budget proposal", $blue);
    imagestring($image, $font, 56, 140, "- Schedule follow-up", $blue);

    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    return $imageData;
}

echo "=== Summary ===\n";
echo "OCR capabilities demonstrated:\n";
echo "1. Basic text extraction from images\n";
echo "2. Structured data extraction (invoices, forms)\n";
echo "3. Multi-language document processing\n";
echo "4. Table data extraction and formatting\n";
echo "5. Visual Q&A about document content\n";
echo "6. Handwriting recognition\n";
```

## Expected Output

```
=== Example 1: Basic OCR ===

Extracted Text:
INVOICE #2024-001
Date: January 15, 2024
Bill To: Acme Corporation
Item: Professional Services
Amount: $1,500.00
Total Due: $1,500.00

=== Example 2: Invoice Analysis ===

Invoice Data:
{
    "invoice_number": "2024-001",
    "date": "January 15, 2024",
    "total_amount": 1500.00,
    "vendor_name": "Acme Corporation",
    "line_items": [
        {
            "description": "Professional Services",
            "amount": 1500.00
        }
    ]
}

[Additional examples with table extraction and Q&A results...]
```

## Try It Yourself

### Exercise 1: Build a Receipt Scanner

Create a receipt processing system:

```php
class ReceiptScanner {
    public function scanReceipt(string $imagePath): array
    {
        return [
            'merchant' => $this->extractMerchant($imagePath),
            'total' => $this->extractTotal($imagePath),
            'date' => $this->extractDate($imagePath),
            'items' => $this->extractLineItems($imagePath),
        ];
    }
}
```

### Exercise 2: Form Validator

Validate extracted data against expected formats:

```php
class FormValidator {
    public function validateExtraction(array $extracted, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            if (!$this->validate($extracted[$field], $rule)) {
                $errors[] = "Field {$field} validation failed";
            }
        }
        return $errors;
    }
}
```

### Exercise 3: Batch Document Processor

Process multiple documents efficiently:

```php
function batchProcessDocuments(array $files): array
{
    $results = [];
    foreach (array_chunk($files, 5) as $batch) {
        // Process batch in parallel
        $results = array_merge($results, processBatch($batch));
    }
    return $results;
}
```

## Troubleshooting

### Issue: Poor OCR Accuracy

- **Solution**: Ensure high image quality (300+ DPI)
- Preprocess images (contrast, rotation correction)
- Use appropriate model parameters

### Issue: Large File Handling

- **Solution**: Compress images before encoding
- Split PDFs into individual pages
- Use streaming for large documents

### Issue: Complex Layouts Not Recognized

- **Solution**: Provide specific instructions in prompts
- Break complex documents into sections
- Use visual Q&A for targeted extraction

### Issue: Slow Processing

- **Solution**: Optimize image size and format
- Cache processed results
- Batch similar documents together

## Next Steps

Continue exploring with:

1. **[08-audio](../08-audio)**: Transcribe audio content
2. **[09-moderation](../09-moderation)**: Moderate extracted content
3. **[06-embeddings](../06-embeddings)**: Create searchable document indexes

### Further Reading

- [Pixtral Model Documentation](https://docs.mistral.ai/capabilities/vision)
- [Image Processing Best Practices](https://docs.mistral.ai/guides/vision)
- [Document Intelligence Patterns](https://azure.microsoft.com/en-us/products/ai-services/ai-document-intelligence)

### Advanced Applications

- **Invoice Automation**: Automatic invoice processing and approval
- **Form Digitization**: Convert paper forms to digital workflows
- **Document Classification**: Automatically categorize documents
- **Data Extraction Pipeline**: Build end-to-end document processing
- **Compliance Checking**: Verify documents meet requirements

Remember: OCR accuracy depends heavily on image quality. Always validate extracted data and implement error handling for
production systems!
