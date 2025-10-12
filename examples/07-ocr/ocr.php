<?php

/**
 * OCR (Optical Character Recognition)
 *
 * Description: Extract text and structured data from documents and images
 * Use Case: Document processing, data extraction, form parsing, invoice processing
 * Prerequisites: MISTRAL_API_KEY in .env file
 *
 * @see https://docs.mistral.ai/capabilities/vision/
 */

declare(strict_types=1);

require_once __DIR__.'/../shared/bootstrap.php';

use HelgeSverre\Mistral\Mistral;

/**
 * Main execution function
 */
function main(): void
{
    displayTitle('OCR (Optical Character Recognition)', '📄');

    $mistral = createMistralClient();

    try {
        // Example 1: Basic OCR from URL
        basicOCR($mistral);

        // Example 2: Multi-page document processing
        multiPageOCR($mistral);

        // Example 3: Structured data extraction
        structuredExtraction($mistral);

        // Example 4: OCR with synthesis (combining OCR with AI)
        ocrWithSynthesis($mistral);

    } catch (Throwable $e) {
        handleError($e);
    }
}

/**
 * Example 1: Basic OCR from document URL
 */
function basicOCR(Mistral $mistral): void
{
    displaySection('Example 1: Basic OCR');
    echo "Extracting text from a PDF document...\n\n";

    // OCR supports PDF, images (PNG, JPG), and other document formats
    // You can provide documents via URL or base64 encoding

    // Sample document URL (replace with your actual document)
    $documentUrl = 'https://arxiv.org/pdf/2401.04088.pdf'; // Sample academic paper

    echo "📄 Document URL: {$documentUrl}\n";
    echo "🔄 Processing document...\n\n";

    // Process the document with OCR
    $response = measureTime(
        callback: fn () => $mistral->ocr()->processUrl(
            url: $documentUrl,
            model: 'pixtral-12b-2409', // Mistral's vision model
            includeImageBase64: false, // Don't include image data in response
        ),
        label: 'OCR Processing',
    );

    $dto = $response->dto();

    echo "✅ OCR completed successfully\n\n";

    echo "📊 Document Information:\n";
    echo '  • Total pages: '.count($dto->pages)."\n";
    echo "  • Model used: {$dto->model}\n";
    if ($dto->usageInfo) {
        echo "  • Pages processed: {$dto->usageInfo->pagesProcessed}\n";
        echo '  • Document size: '.formatBytes($dto->usageInfo->docSizeBytes)."\n";
    }
    echo "\n";

    // Display text from first page
    if (! empty($dto->pages)) {
        $firstPage = $dto->pages[0];

        echo "📝 First Page Content (preview):\n";
        echo str_repeat('─', 60)."\n";

        // Get the markdown content
        $content = $firstPage->markdown ?? '';
        $preview = substr($content, 0, 500);
        echo $preview;

        if (strlen($content) > 500) {
            echo "\n... (truncated, total ".strlen($content)." characters)\n";
        }

        echo "\n".str_repeat('─', 60)."\n\n";

        // Display page metadata
        echo "📐 Page Dimensions:\n";
        if ($firstPage->dimensions) {
            echo "  • Width: {$firstPage->dimensions->width}px\n";
            echo "  • Height: {$firstPage->dimensions->height}px\n";
        }

        // Display images if any
        if (! empty($firstPage->images)) {
            echo "\n🖼️ Images detected: ".count($firstPage->images)."\n";
            foreach ($firstPage->images as $i => $image) {
                echo '  Image '.($i + 1).":\n";
                if ($image->dimensions) {
                    echo "    • Size: {$image->dimensions->width}x{$image->dimensions->height}px\n";
                }
            }
        }
    }

    echo "\n💡 OCR Best Practices:\n";
    echo "  • Use clear, high-resolution documents\n";
    echo "  • PDF format works best for multi-page documents\n";
    echo "  • Consider document size for token usage\n";
    echo "  • Cache results to avoid reprocessing\n\n";
}

/**
 * Example 2: Processing multi-page documents
 */
function multiPageOCR(Mistral $mistral): void
{
    displaySection('Example 2: Multi-page Document');
    echo "Processing and analyzing a multi-page document...\n\n";

    // Sample multi-page document
    $documentUrl = 'https://arxiv.org/pdf/2401.04088.pdf';

    echo "📄 Processing document: {$documentUrl}\n";
    echo "🔄 Extracting content from all pages...\n\n";

    $response = $mistral->ocr()->processUrl(
        url: $documentUrl,
        model: 'pixtral-12b-2409',
    );

    $dto = $response->dto();

    echo "✅ Document processed\n\n";

    echo "📚 Page-by-Page Analysis:\n";
    echo str_repeat('─', 60)."\n\n";

    $totalCharacters = 0;
    $totalImages = 0;

    foreach ($dto->pages as $pageIndex => $page) {
        $pageNumber = $pageIndex + 1;
        $content = $page->markdown ?? '';
        $charCount = strlen($content);
        $imageCount = count($page->images ?? []);

        $totalCharacters += $charCount;
        $totalImages += $imageCount;

        echo "Page {$pageNumber}:\n";
        echo "  • Characters: {$charCount}\n";
        echo "  • Images: {$imageCount}\n";

        if ($page->dimensions) {
            echo "  • Dimensions: {$page->dimensions->width}x{$page->dimensions->height}px\n";
        }

        // Show first 100 characters of each page
        $preview = substr($content, 0, 100);
        echo '  • Preview: '.str_replace("\n", ' ', $preview)."...\n\n";
    }

    echo str_repeat('─', 60)."\n";
    echo "📊 Document Summary:\n";
    echo '  • Total pages: '.count($dto->pages)."\n";
    echo "  • Total characters: {$totalCharacters}\n";
    echo "  • Total images: {$totalImages}\n";
    echo '  • Average chars/page: '.round($totalCharacters / max(count($dto->pages), 1))."\n\n";

    echo "💡 Multi-page Processing Tips:\n";
    echo "  • Process pages in batches for large documents\n";
    echo "  • Monitor token usage across all pages\n";
    echo "  • Extract specific pages if full processing is expensive\n";
    echo "  • Cache processed results for reuse\n\n";
}

/**
 * Example 3: Structured data extraction from documents
 */
function structuredExtraction(Mistral $mistral): void
{
    displaySection('Example 3: Structured Data Extraction');
    echo "Extracting structured information from documents...\n\n";

    // Sample invoice or form document
    $documentUrl = 'https://arxiv.org/pdf/2401.04088.pdf';

    echo "📄 Document: {$documentUrl}\n";
    echo "🔄 Processing for structured data extraction...\n\n";

    // First, get the OCR content
    $ocrResponse = $mistral->ocr()->processUrl(
        url: $documentUrl,
        model: 'pixtral-12b-2409',
    );

    $ocrDto = $ocrResponse->dto();

    // Get the full text content
    $fullText = '';
    foreach ($ocrDto->pages as $page) {
        $fullText .= $page->markdown."\n\n";
    }

    echo '✅ OCR completed, extracted '.strlen($fullText)." characters\n\n";

    // Now use AI to extract structured data from the text
    echo "🤖 Using AI to extract structured information...\n\n";

    $messages = [
        [
            'role' => 'system',
            'content' => 'You are a document analysis assistant. Extract key information from documents in JSON format.',
        ],
        [
            'role' => 'user',
            'content' => "Analyze this document and extract:\n".
                "1. Document title\n".
                "2. Authors (if any)\n".
                "3. Main topics/keywords\n".
                "4. Document type (paper, invoice, form, etc.)\n".
                "5. Key dates (if any)\n\n".
                "Document content:\n{$fullText}",
        ],
    ];

    $aiResponse = $mistral->chat()->create(
        messages: $messages,
        model: 'mistral-small-latest',
        maxTokens: 1000,
        responseFormat: ['type' => 'json_object'],
    );

    $extractedData = $aiResponse->dto()->choices[0]->message->content;

    echo "📋 Extracted Structured Data:\n";
    echo str_repeat('─', 60)."\n";

    // Parse and display the JSON
    $parsed = json_decode($extractedData, true);
    if ($parsed) {
        printJson($parsed, 'Document Metadata');
    } else {
        echo $extractedData."\n\n";
    }

    echo "💡 Structured Extraction Use Cases:\n";
    echo "  • Invoice processing (amounts, dates, vendors)\n";
    echo "  • Form data extraction (fields, values)\n";
    echo "  • Resume parsing (skills, experience)\n";
    echo "  • Contract analysis (terms, parties, dates)\n";
    echo "  • Receipt digitization (items, prices)\n\n";
}

/**
 * Example 4: OCR with AI synthesis
 */
function ocrWithSynthesis(Mistral $mistral): void
{
    displaySection('Example 4: OCR with AI Synthesis');
    echo "Combining OCR with AI to answer questions about documents...\n\n";

    $documentUrl = 'https://arxiv.org/pdf/2401.04088.pdf';

    echo "📄 Document: {$documentUrl}\n";
    echo "🔄 Step 1: Extract text with OCR...\n\n";

    // Step 1: Extract text with OCR
    $ocrResponse = $mistral->ocr()->processUrl(
        url: $documentUrl,
        model: 'pixtral-12b-2409',
    );

    $ocrDto = $ocrResponse->dto();

    // Collect all text
    $documentText = '';
    foreach ($ocrDto->pages as $page) {
        $documentText .= $page->markdown."\n\n";
    }

    echo '✅ Extracted '.strlen($documentText)." characters\n\n";

    // Step 2: Ask questions about the document
    echo "🔄 Step 2: Answer questions using AI...\n\n";

    $questions = [
        'What is the main topic of this document?',
        'Summarize the key findings in 2-3 sentences.',
        'What methodology or approach is described?',
    ];

    foreach ($questions as $i => $question) {
        echo '❓ Question '.($i + 1).": {$question}\n";
        echo str_repeat('─', 60)."\n";

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a document analysis assistant. Answer questions based solely on the provided document content.',
            ],
            [
                'role' => 'user',
                'content' => "Document content:\n{$documentText}\n\nQuestion: {$question}",
            ],
        ];

        $response = $mistral->chat()->create(
            messages: $messages,
            model: 'mistral-small-latest',
            maxTokens: 300,
        );

        $answer = $response->dto()->choices[0]->message->content;
        echo "💬 Answer: {$answer}\n\n";
    }

    echo "💡 OCR + AI Synthesis Benefits:\n";
    echo "  • Intelligent document Q&A\n";
    echo "  • Automated document summarization\n";
    echo "  • Smart form filling\n";
    echo "  • Document comparison and analysis\n";
    echo "  • Compliance checking\n\n";

    echo "🚀 Production Considerations:\n";
    echo "  • Cache OCR results to avoid reprocessing\n";
    echo "  • Chunk large documents for better context\n";
    echo "  • Use embeddings for document search\n";
    echo "  • Implement error handling for poor quality scans\n";
    echo "  • Monitor token usage across OCR + chat\n";
    echo "  • Consider document preprocessing (cleanup, enhancement)\n";
    echo "  • Store processed documents for audit trails\n";
}

// Run the example if executed directly
if (php_sapi_name() === 'cli') {
    main();
}
