<?php

/**
 * Text Embeddings
 *
 * Description: Generate and use vector embeddings for semantic search and similarity
 * Use Case: Semantic search, recommendation systems, clustering, RAG applications
 * Prerequisites: MISTRAL_API_KEY in .env file
 *
 * @see https://docs.mistral.ai/capabilities/embeddings/
 */

declare(strict_types=1);

require_once __DIR__.'/../shared/bootstrap.php';

use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Mistral;

/**
 * Main execution function
 */
function main(): void
{
    displayTitle('Text Embeddings', '🔢');

    $mistral = createMistralClient();

    try {
        // Example 1: Generate embeddings for texts
        generateEmbeddings($mistral);

        // Example 2: Calculate similarity between texts
        calculateSimilarity($mistral);

        // Example 3: Semantic search
        semanticSearch($mistral);

        // Example 4: Text clustering
        textClustering($mistral);

    } catch (Throwable $e) {
        handleError($e);
    }
}

/**
 * Example 1: Generate embeddings for text inputs
 */
function generateEmbeddings(Mistral $mistral): void
{
    displaySection('Example 1: Generating Embeddings');
    echo "Creating vector embeddings for text...\n\n";

    // Embeddings convert text into high-dimensional vectors
    // These vectors capture semantic meaning
    // Similar texts have similar vectors

    $texts = [
        'The cat sleeps on the couch.',
        'A feline rests on the sofa.',
        'The weather is sunny today.',
    ];

    echo "Input texts:\n";
    foreach ($texts as $i => $text) {
        echo '  '.($i + 1).". {$text}\n";
    }
    echo "\n";

    // Generate embeddings for all texts
    $response = $mistral->embedding()->create(
        input: $texts,
        model: Model::embed->value,
    );

    $dto = $response->dto();

    echo "✅ Embeddings generated successfully\n\n";

    echo "📊 Embedding Details:\n";
    echo "  • Model: {$dto->model}\n";
    echo '  • Number of embeddings: '.count($dto->data)."\n";

    $firstItem = $dto->data->first();
    if ($firstItem && $firstItem->embedding) {
        echo '  • Vector dimensions: '.count($firstItem->embedding)."\n";
        echo "  • Token usage: {$dto->usage->totalTokens} tokens\n\n";

        // Display first few dimensions of first embedding
        echo "🔢 First embedding (first 10 dimensions):\n";
        $firstEmbedding = array_slice($firstItem->embedding, 0, 10);
        foreach ($firstEmbedding as $i => $value) {
            echo "  [{$i}]: ".number_format($value, 6)."\n";
        }
        echo '  ... ('.count($firstItem->embedding)." dimensions total)\n\n";
    } else {
        echo "❌ No embedding data received\n\n";
    }

    echo "💡 Understanding Embeddings:\n";
    echo "  • Each text becomes a vector of numbers\n";
    echo "  • Vector length is fixed (e.g., 1024 dimensions)\n";
    echo "  • Similar texts have similar vectors\n";
    echo "  • Use cosine similarity to compare vectors\n";
    echo "  • Store vectors in vector databases for scale\n\n";
}

/**
 * Example 2: Calculate similarity between texts
 */
function calculateSimilarity(Mistral $mistral): void
{
    displaySection('Example 2: Similarity Calculation');
    echo "Comparing texts using cosine similarity...\n\n";

    // Create a set of texts to compare
    $texts = [
        'Machine learning is a subset of artificial intelligence',
        'AI and machine learning are related technologies',
        'I love pizza and pasta',
        'Italian food is delicious',
        'The stock market crashed yesterday',
    ];

    echo "Texts to compare:\n";
    foreach ($texts as $i => $text) {
        echo '  '.($i + 1).". {$text}\n";
    }
    echo "\n";

    // Generate embeddings
    $response = $mistral->embedding()->create(
        input: $texts,
        model: Model::embed->value,
    );

    $dto = $response->dto();
    // Extract embeddings from DataCollection
    $embeddings = [];
    foreach ($dto->data as $item) {
        $embeddings[] = $item->embedding ?? [];
    }

    // Calculate similarity matrix
    echo "📊 Similarity Matrix:\n";
    echo str_repeat('─', 60)."\n";
    echo "Comparing each text pair (1.0 = identical, 0.0 = unrelated)\n\n";

    // Compare first text with all others
    $baseIndex = 0;
    echo "Base text: \"{$texts[$baseIndex]}\"\n\n";

    for ($i = 0; $i < count($texts); $i++) {
        if ($i === $baseIndex) {
            continue;
        }

        $similarity = cosineSimilarity($embeddings[$baseIndex], $embeddings[$i]);
        $similarityPercent = round($similarity * 100, 2);

        // Visual bar representation
        $barLength = (int) ($similarity * 20);
        $bar = str_repeat('█', $barLength).str_repeat('░', 20 - $barLength);

        echo 'Text '.($i + 1).": [{$bar}] {$similarityPercent}%\n";
        echo "  \"{$texts[$i]}\"\n\n";
    }

    echo "💡 Interpretation:\n";
    echo "  • Texts 1-2: High similarity (both about AI/ML)\n";
    echo "  • Texts 3-4: Medium-high similarity (both about food)\n";
    echo "  • Text 5: Low similarity (different topic)\n";
    echo "  • Similarity > 0.8: Very similar\n";
    echo "  • Similarity 0.5-0.8: Related\n";
    echo "  • Similarity < 0.5: Different topics\n\n";
}

/**
 * Example 3: Semantic search implementation
 */
function semanticSearch(Mistral $mistral): void
{
    displaySection('Example 3: Semantic Search');
    echo "Implementing semantic search over a document collection...\n\n";

    // Simulated document collection
    $documents = [
        'Python is a high-level programming language used for web development, data science, and automation.',
        'JavaScript is the language of the web, running in browsers and on servers with Node.js.',
        'PHP is a server-side scripting language designed for web development.',
        'The Great Pyramid of Giza is one of the Seven Wonders of the Ancient World.',
        'Mount Everest is the highest mountain in the world, located in the Himalayas.',
        'The Eiffel Tower is an iconic landmark in Paris, France.',
        'Machine learning models can predict outcomes based on historical data patterns.',
        'Neural networks are inspired by the human brain and used in deep learning.',
        'Quantum computing uses quantum mechanics to process information.',
    ];

    echo '📚 Document Collection ({count: '.count($documents)."}):\n";
    foreach ($documents as $i => $doc) {
        echo '  ['.($i + 1).'] '.substr($doc, 0, 60)."...\n";
    }
    echo "\n";

    // Generate embeddings for all documents
    echo "🔄 Generating document embeddings...\n";

    $docResponse = $mistral->embedding()->create(
        input: $documents,
        model: Model::embed->value,
    );

    // Extract embeddings from DataCollection
    $docEmbeddings = [];
    foreach ($docResponse->dto()->data as $item) {
        $docEmbeddings[] = $item->embedding ?? [];
    }
    echo "✅ Document embeddings cached\n\n";

    // Search queries
    $queries = [
        'programming languages for web development',
        'famous landmarks and monuments',
        'artificial intelligence and learning',
    ];

    foreach ($queries as $query) {
        echo "🔍 Search Query: \"{$query}\"\n";
        echo str_repeat('─', 60)."\n";

        // Generate query embedding
        $queryResponse = $mistral->embedding()->create(
            input: [$query],
            model: Model::embed->value,
        );

        $queryDto = $queryResponse->dto();
        $firstQueryItem = $queryDto->data->first();
        if (! $firstQueryItem || ! $firstQueryItem->embedding) {
            echo "❌ No embedding received for query\n\n";

            continue;
        }
        $queryEmbedding = $firstQueryItem->embedding;

        // Calculate similarity with all documents
        $results = [];
        foreach ($docEmbeddings as $i => $docEmbed) {
            $similarity = cosineSimilarity($queryEmbedding, $docEmbed);
            $results[] = [
                'index' => $i,
                'document' => $documents[$i],
                'similarity' => $similarity,
            ];
        }

        // Sort by similarity (highest first)
        usort($results, fn ($a, $b) => $b['similarity'] <=> $a['similarity']);

        // Display top 3 results
        echo "Top 3 Results:\n\n";
        for ($i = 0; $i < 3; $i++) {
            $result = $results[$i];
            $score = round($result['similarity'] * 100, 2);

            echo '  Rank '.($i + 1).": Score {$score}%\n";
            echo "  {$result['document']}\n\n";
        }
    }

    echo "💡 Semantic Search Benefits:\n";
    echo "  • Finds conceptually similar content, not just keyword matches\n";
    echo "  • Works across different phrasings and languages\n";
    echo "  • No need for exact keyword matches\n";
    echo "  • Handles synonyms and related concepts\n";
    echo "  • More relevant results than traditional search\n\n";
}

/**
 * Example 4: Text clustering by similarity
 */
function textClustering(Mistral $mistral): void
{
    displaySection('Example 4: Text Clustering');
    echo "Grouping similar texts using embeddings...\n\n";

    // Mixed collection of texts on different topics
    $texts = [
        'The cat sat on the mat',
        'Dogs are loyal pets',
        'Cats are independent animals',
        'Machine learning algorithms learn from data',
        'AI models require training data',
        'Neural networks mimic brain structure',
        'Paris is the capital of France',
        'The Eiffel Tower is in Paris',
        'London is known for Big Ben',
    ];

    echo '📝 Texts to cluster ({count: '.count($texts)."}):\n";
    foreach ($texts as $i => $text) {
        echo '  ['.($i + 1)."] {$text}\n";
    }
    echo "\n";

    // Generate embeddings
    echo "🔄 Generating embeddings...\n";
    $response = $mistral->embedding()->create(
        input: $texts,
        model: Model::embed->value,
    );

    // Extract embeddings from DataCollection
    $embeddings = [];
    foreach ($response->dto()->data as $item) {
        $embeddings[] = $item->embedding ?? [];
    }
    echo "✅ Embeddings generated\n\n";

    // Simple clustering: find groups of similar texts
    echo "🔍 Identifying clusters:\n";
    echo str_repeat('─', 60)."\n\n";

    $clusters = [];
    $used = [];

    for ($i = 0; $i < count($texts); $i++) {
        if (in_array($i, $used)) {
            continue;
        }

        $cluster = [$i];
        $used[] = $i;

        // Find similar texts (similarity > 0.6)
        for ($j = $i + 1; $j < count($texts); $j++) {
            if (in_array($j, $used)) {
                continue;
            }

            $similarity = cosineSimilarity($embeddings[$i], $embeddings[$j]);
            if ($similarity > 0.6) {
                $cluster[] = $j;
                $used[] = $j;
            }
        }

        $clusters[] = $cluster;
    }

    // Display clusters
    foreach ($clusters as $clusterIndex => $cluster) {
        echo '📁 Cluster '.($clusterIndex + 1).' ('.count($cluster)." texts):\n";
        foreach ($cluster as $textIndex) {
            echo "  • {$texts[$textIndex]}\n";
        }

        // Determine cluster theme
        $firstText = strtolower($texts[$cluster[0]]);
        $theme = 'Unknown';
        if (str_contains($firstText, 'cat') || str_contains($firstText, 'dog')) {
            $theme = '🐾 Animals/Pets';
        } elseif (str_contains($firstText, 'machine') || str_contains($firstText, 'ai') || str_contains($firstText, 'neural')) {
            $theme = '🤖 AI/Technology';
        } elseif (str_contains($firstText, 'paris') || str_contains($firstText, 'london') || str_contains($firstText, 'eiffel')) {
            $theme = '🏛️ Cities/Landmarks';
        }

        echo "  Theme: {$theme}\n\n";
    }

    echo "💡 Clustering Use Cases:\n";
    echo "  • Organize large document collections\n";
    echo "  • Group similar customer feedback\n";
    echo "  • Categorize support tickets\n";
    echo "  • Find duplicate or near-duplicate content\n";
    echo "  • Discover topics in unstructured data\n\n";

    echo "🚀 Production Tips:\n";
    echo "  • Use vector databases (Pinecone, Weaviate, Qdrant)\n";
    echo "  • Implement proper clustering algorithms (K-means, DBSCAN)\n";
    echo "  • Cache embeddings to avoid regeneration\n";
    echo "  • Batch embedding requests for efficiency\n";
    echo "  • Monitor token usage for cost optimization\n";
    echo "  • Consider dimensionality reduction for visualization\n";
}

// Run the example if executed directly
if (php_sapi_name() === 'cli') {
    main();
}
