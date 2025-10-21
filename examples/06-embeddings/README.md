# Text Embeddings with Mistral PHP SDK

## Overview

This example explores Mistral's embedding capabilities for converting text into high-dimensional vector representations.
These embeddings capture semantic meaning and enable powerful features like semantic search, similarity matching,
clustering, and recommendation systems. You'll learn how to generate embeddings and use them for practical applications.

### Real-world Use Cases

- Semantic search engines
- Document similarity detection
- Content recommendation systems
- Duplicate detection
- Question-answering systems
- Knowledge base organization

### Prerequisites

- Completed [05-function-calling](../05-function-calling) example
- Basic understanding of vector mathematics
- Familiarity with similarity measures (cosine, euclidean)
- Optional: Knowledge of vector databases

## Concepts

### What Are Embeddings?

Embeddings are numerical representations of text that capture semantic meaning:

- **Dense Vectors**: Arrays of floating-point numbers (typically 1024 dimensions)
- **Semantic Similarity**: Similar texts have similar vectors
- **Language Agnostic**: Meaning transcends specific words

### Mistral's Embedding Model

- **Model**: `mistral-embed` (1024 dimensions)
- **Max Input**: 8,192 tokens
- **Use Cases**: Optimized for retrieval and similarity tasks
- **Performance**: Fast and efficient for production use

### Similarity Metrics

- **Cosine Similarity**: Measures angle between vectors (0 to 1, higher is more similar)
- **Euclidean Distance**: Measures straight-line distance (lower is more similar)
- **Dot Product**: Combines magnitude and direction

## Implementation

### Basic Embedding Generation

Generate embeddings for single or multiple texts:

```php
<?php

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Embeddings\EmbeddingRequest;

$mistral = new Mistral($_ENV['MISTRAL_API_KEY']);

// Single text embedding
$request = EmbeddingRequest::from([
    'model' => 'mistral-embed',
    'input' => 'The quick brown fox jumps over the lazy dog.',
]);

$dto = $mistral->embeddings()->createDto($request);
$embedding = $dto->data[0]->embedding; // Array of 1024 floats

echo "Embedding dimensions: " . count($embedding) . "\n";
echo "First 5 values: " . implode(', ', array_slice($embedding, 0, 5)) . "\n";

// Batch embeddings
$texts = [
    'PHP is a popular web development language.',
    'Python is widely used for data science.',
    'JavaScript runs in web browsers.',
];

$request = EmbeddingRequest::from([
    'model' => 'mistral-embed',
    'input' => $texts,
]);

$dto = $mistral->embeddings()->createDto($request);
echo "Generated " . count($dto->data) . " embeddings\n";
```

### Semantic Search Implementation

Build a simple semantic search engine:

```php
class SemanticSearch
{
    private Mistral $client;
    private array $documents = [];
    private array $embeddings = [];

    public function __construct(string $apiKey)
    {
        $this->client = new Mistral($apiKey);
    }

    public function indexDocuments(array $documents): void
    {
        $this->documents = $documents;

        // Generate embeddings for all documents
        $request = EmbeddingRequest::from([
            'model' => 'mistral-embed',
            'input' => $documents,
        ]);

        $dto = $this->client->embeddings()->createDto($request);

        // Store embeddings
        foreach ($dto->data as $embedding) {
            $this->embeddings[] = $embedding->embedding;
        }
    }

    public function search(string $query, int $topK = 5): array
    {
        // Generate query embedding
        $request = EmbeddingRequest::from([
            'model' => 'mistral-embed',
            'input' => $query,
        ]);

        $dto = $this->client->embeddings()->createDto($request);
        $queryEmbedding = $dto->data[0]->embedding;

        // Calculate similarities
        $similarities = [];
        foreach ($this->embeddings as $index => $docEmbedding) {
            $similarities[$index] = $this->cosineSimilarity(
                $queryEmbedding,
                $docEmbedding
            );
        }

        // Sort by similarity
        arsort($similarities);

        // Return top K results
        $results = [];
        foreach (array_slice($similarities, 0, $topK, true) as $index => $score) {
            $results[] = [
                'document' => $this->documents[$index],
                'score' => $score,
                'index' => $index,
            ];
        }

        return $results;
    }

    private function cosineSimilarity(array $vec1, array $vec2): float
    {
        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        for ($i = 0; $i < count($vec1); $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $norm1 += $vec1[$i] * $vec1[$i];
            $norm2 += $vec2[$i] * $vec2[$i];
        }

        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);

        if ($norm1 == 0 || $norm2 == 0) {
            return 0;
        }

        return $dotProduct / ($norm1 * $norm2);
    }
}
```

### Document Clustering

Group similar documents together:

```php
class DocumentClusterer
{
    private Mistral $client;
    private array $embeddings = [];

    public function __construct(string $apiKey)
    {
        $this->client = new Mistral($apiKey);
    }

    public function clusterDocuments(array $documents, int $numClusters = 3): array
    {
        // Generate embeddings
        $request = EmbeddingRequest::from([
            'model' => 'mistral-embed',
            'input' => $documents,
        ]);

        $dto = $this->client->embeddings()->createDto($request);

        foreach ($dto->data as $embedding) {
            $this->embeddings[] = $embedding->embedding;
        }

        // Simple k-means clustering
        $clusters = $this->kMeans($this->embeddings, $numClusters);

        // Group documents by cluster
        $result = array_fill(0, $numClusters, []);
        foreach ($clusters as $docIndex => $clusterIndex) {
            $result[$clusterIndex][] = [
                'document' => $documents[$docIndex],
                'index' => $docIndex,
            ];
        }

        return $result;
    }

    private function kMeans(array $points, int $k, int $maxIterations = 100): array
    {
        $n = count($points);
        $dim = count($points[0]);

        // Initialize centroids randomly
        $centroids = [];
        $used = [];
        for ($i = 0; $i < $k; $i++) {
            do {
                $idx = rand(0, $n - 1);
            } while (in_array($idx, $used));
            $used[] = $idx;
            $centroids[] = $points[$idx];
        }

        $assignments = array_fill(0, $n, 0);

        for ($iter = 0; $iter < $maxIterations; $iter++) {
            $changed = false;

            // Assign points to nearest centroid
            for ($i = 0; $i < $n; $i++) {
                $minDist = PHP_FLOAT_MAX;
                $minIndex = 0;

                for ($j = 0; $j < $k; $j++) {
                    $dist = $this->euclideanDistance($points[$i], $centroids[$j]);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $minIndex = $j;
                    }
                }

                if ($assignments[$i] != $minIndex) {
                    $assignments[$i] = $minIndex;
                    $changed = true;
                }
            }

            if (!$changed) break;

            // Update centroids
            for ($j = 0; $j < $k; $j++) {
                $cluster = [];
                for ($i = 0; $i < $n; $i++) {
                    if ($assignments[$i] == $j) {
                        $cluster[] = $points[$i];
                    }
                }

                if (!empty($cluster)) {
                    $centroids[$j] = $this->calculateCentroid($cluster);
                }
            }
        }

        return $assignments;
    }

    private function euclideanDistance(array $vec1, array $vec2): float
    {
        $sum = 0;
        for ($i = 0; $i < count($vec1); $i++) {
            $sum += pow($vec1[$i] - $vec2[$i], 2);
        }
        return sqrt($sum);
    }

    private function calculateCentroid(array $points): array
    {
        $dim = count($points[0]);
        $centroid = array_fill(0, $dim, 0);

        foreach ($points as $point) {
            for ($i = 0; $i < $dim; $i++) {
                $centroid[$i] += $point[$i];
            }
        }

        $n = count($points);
        for ($i = 0; $i < $dim; $i++) {
            $centroid[$i] /= $n;
        }

        return $centroid;
    }
}
```

## Code Example

Complete working example (`embeddings.php`):

```php
<?php

require_once 'vendor/autoload.php';

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Embeddings\EmbeddingRequest;

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');
if (!$apiKey) {
    die("Please set your MISTRAL_API_KEY environment variable\n");
}

$mistral = new Mistral($apiKey);

// Example 1: Basic Embedding Generation
echo "=== Example 1: Basic Embeddings ===\n\n";

$text = "Artificial intelligence is transforming the world of technology.";
$request = EmbeddingRequest::from([
    'model' => 'mistral-embed',
    'input' => $text,
]);

$dto = $mistral->embeddings()->createDto($request);
$embedding = $dto->data[0]->embedding;

echo "Text: \"{$text}\"\n";
echo "Embedding dimensions: " . count($embedding) . "\n";
echo "Sample values: [" . implode(', ', array_map(function($v) {
    return number_format($v, 4);
}, array_slice($embedding, 0, 5))) . ", ...]\n";
echo "Vector magnitude: " . number_format(sqrt(array_sum(array_map(function($v) {
    return $v * $v;
}, $embedding))), 4) . "\n\n";

// Example 2: Similarity Comparison
echo "=== Example 2: Semantic Similarity ===\n\n";

$sentences = [
    "The cat sat on the mat.",
    "A feline rested on the rug.",
    "The dog played in the garden.",
    "Python is a programming language.",
    "PHP is used for web development.",
];

// Generate embeddings for all sentences
$request = EmbeddingRequest::from([
    'model' => 'mistral-embed',
    'input' => $sentences,
]);

$dto = $mistral->embeddings()->createDto($request);
$embeddings = array_map(fn($d) => $d->embedding, $dto->data);

// Calculate similarity matrix
echo "Similarity Matrix (cosine similarity):\n\n";
echo str_pad("", 10);
foreach (range(0, count($sentences) - 1) as $i) {
    echo str_pad("S" . ($i + 1), 8);
}
echo "\n";

for ($i = 0; $i < count($sentences); $i++) {
    echo str_pad("S" . ($i + 1) . ":", 10);
    for ($j = 0; $j < count($sentences); $j++) {
        $similarity = cosineSimilarity($embeddings[$i], $embeddings[$j]);
        echo str_pad(number_format($similarity, 3), 8);
    }
    echo "  " . substr($sentences[$i], 0, 30) . "...\n";
}

echo "\nMost similar pairs:\n";
$pairs = [];
for ($i = 0; $i < count($sentences); $i++) {
    for ($j = $i + 1; $j < count($sentences); $j++) {
        $similarity = cosineSimilarity($embeddings[$i], $embeddings[$j]);
        $pairs[] = [
            'indices' => [$i, $j],
            'similarity' => $similarity,
        ];
    }
}

usort($pairs, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
foreach (array_slice($pairs, 0, 3) as $pair) {
    $i = $pair['indices'][0];
    $j = $pair['indices'][1];
    echo "  S" . ($i + 1) . " & S" . ($j + 1) . ": " .
         number_format($pair['similarity'], 3) . "\n";
}

// Example 3: Semantic Search
echo "\n=== Example 3: Semantic Search ===\n\n";

$knowledgeBase = [
    "PHP is a server-side scripting language designed for web development.",
    "Laravel is a PHP web application framework with expressive syntax.",
    "MySQL is an open-source relational database management system.",
    "Redis is an in-memory data structure store used as a cache.",
    "Docker is a platform for developing and running applications in containers.",
    "Kubernetes orchestrates containerized applications at scale.",
    "Git is a distributed version control system for tracking code changes.",
    "Composer is a dependency manager for PHP projects.",
];

// Index the knowledge base
echo "Indexing " . count($knowledgeBase) . " documents...\n\n";

$request = EmbeddingRequest::from([
    'model' => 'mistral-embed',
    'input' => $knowledgeBase,
]);

$dto = $mistral->embeddings()->createDto($request);
$docEmbeddings = array_map(fn($d) => $d->embedding, $dto->data);

// Perform searches
$queries = [
    "How do I manage PHP packages?",
    "What database should I use?",
    "Tell me about container technology",
];

foreach ($queries as $query) {
    echo "Query: \"{$query}\"\n";

    // Get query embedding
    $request = EmbeddingRequest::from([
        'model' => 'mistral-embed',
        'input' => $query,
    ]);

    $dto = $mistral->embeddings()->createDto($request);
    $queryEmbedding = $dto->data[0]->embedding;

    // Find most similar documents
    $similarities = [];
    foreach ($docEmbeddings as $i => $docEmb) {
        $similarities[$i] = cosineSimilarity($queryEmbedding, $docEmb);
    }

    arsort($similarities);
    $topResults = array_slice($similarities, 0, 2, true);

    echo "Top results:\n";
    foreach ($topResults as $index => $score) {
        echo "  [" . number_format($score, 3) . "] " .
             $knowledgeBase[$index] . "\n";
    }
    echo "\n";
}

// Example 4: Duplicate Detection
echo "=== Example 4: Duplicate Detection ===\n\n";

$documents = [
    "The quick brown fox jumps over the lazy dog.",
    "A fast auburn fox leaps above a sleepy canine.",
    "Python is great for machine learning applications.",
    "Machine learning tasks are well-suited for Python.",
    "The weather today is sunny and warm.",
    "It's a bright and warm day outside.",
];

$request = EmbeddingRequest::from([
    'model' => 'mistral-embed',
    'input' => $documents,
]);

$dto = $mistral->embeddings()->createDto($request);
$docEmbeddings = array_map(fn($d) => $d->embedding, $dto->data);

echo "Detecting near-duplicates (similarity > 0.85):\n\n";

$threshold = 0.85;
$foundDuplicates = false;

for ($i = 0; $i < count($documents); $i++) {
    for ($j = $i + 1; $j < count($documents); $j++) {
        $similarity = cosineSimilarity($docEmbeddings[$i], $docEmbeddings[$j]);

        if ($similarity > $threshold) {
            $foundDuplicates = true;
            echo "Potential duplicates (similarity: " .
                 number_format($similarity, 3) . "):\n";
            echo "  Doc " . ($i + 1) . ": \"" . $documents[$i] . "\"\n";
            echo "  Doc " . ($j + 1) . ": \"" . $documents[$j] . "\"\n\n";
        }
    }
}

if (!$foundDuplicates) {
    echo "No duplicates found with threshold {$threshold}\n";
}

// Example 5: Question Answering
echo "=== Example 5: Question Answering ===\n\n";

$faq = [
    ['q' => 'How do I install PHP?', 'a' => 'Use package managers like apt, yum, or brew.'],
    ['q' => 'What is Composer?', 'a' => 'Composer is a dependency management tool for PHP.'],
    ['q' => 'How to connect to MySQL?', 'a' => 'Use PDO or mysqli extensions in PHP.'],
    ['q' => 'What is Laravel?', 'a' => 'Laravel is a PHP web application framework.'],
];

// Index questions
$questions = array_column($faq, 'q');
$request = EmbeddingRequest::from([
    'model' => 'mistral-embed',
    'input' => $questions,
]);

$dto = $mistral->embeddings()->createDto($request);
$questionEmbeddings = array_map(fn($d) => $d->embedding, $dto->data);

// User questions
$userQuestions = [
    "How can I set up PHP on my computer?",
    "What tool manages PHP libraries?",
];

foreach ($userQuestions as $userQ) {
    echo "User: \"{$userQ}\"\n";

    // Get embedding
    $request = EmbeddingRequest::from([
        'model' => 'mistral-embed',
        'input' => $userQ,
    ]);

    $dto = $mistral->embeddings()->createDto($request);
    $userEmbedding = $dto->data[0]->embedding;

    // Find best match
    $bestMatch = -1;
    $bestScore = 0;

    foreach ($questionEmbeddings as $i => $qEmb) {
        $score = cosineSimilarity($userEmbedding, $qEmb);
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestMatch = $i;
        }
    }

    if ($bestMatch >= 0 && $bestScore > 0.7) {
        echo "Matched FAQ: \"" . $faq[$bestMatch]['q'] . "\"\n";
        echo "Answer: " . $faq[$bestMatch]['a'] . "\n";
        echo "Confidence: " . number_format($bestScore, 2) . "\n";
    } else {
        echo "No matching FAQ found.\n";
    }
    echo "\n";
}

// Helper function
function cosineSimilarity(array $vec1, array $vec2): float
{
    $dotProduct = 0;
    $norm1 = 0;
    $norm2 = 0;

    for ($i = 0; $i < count($vec1); $i++) {
        $dotProduct += $vec1[$i] * $vec2[$i];
        $norm1 += $vec1[$i] * $vec1[$i];
        $norm2 += $vec2[$i] * $vec2[$i];
    }

    return $dotProduct / (sqrt($norm1) * sqrt($norm2));
}

echo "=== Summary ===\n";
echo "Embeddings enable:\n";
echo "1. Semantic search without keyword matching\n";
echo "2. Document similarity and clustering\n";
echo "3. Duplicate and near-duplicate detection\n";
echo "4. Question answering systems\n";
echo "5. Content recommendation engines\n";
```

## Expected Output

```
=== Example 1: Basic Embeddings ===

Text: "Artificial intelligence is transforming the world of technology."
Embedding dimensions: 1024
Sample values: [0.0234, -0.0156, 0.0089, 0.0412, -0.0267, ...]
Vector magnitude: 1.0000

=== Example 2: Semantic Similarity ===

Similarity Matrix:
          S1      S2      S3      S4      S5
S1:       1.000   0.892   0.654   0.234   0.267
S2:       0.892   1.000   0.623   0.198   0.245
...

Most similar pairs:
  S1 & S2: 0.892 (cat/mat vs feline/rug - semantic similarity)
  S4 & S5: 0.743 (Python vs PHP - both programming languages)

[Additional examples with search results and duplicate detection...]
```

## Try It Yourself

### Exercise 1: Build a Recommendation System

Create content recommendations based on similarity:

```php
class ContentRecommender {
    public function recommend(string $contentId, array $allContent, int $topK = 5): array
    {
        // Get embedding for target content
        // Compare with all other content
        // Return top K most similar items
    }
}
```

### Exercise 2: Implement Semantic Caching

Cache similar queries to reduce API calls:

```php
class SemanticCache {
    private array $cache = [];
    private float $threshold = 0.95;

    public function get(string $query): ?string
    {
        $queryEmbedding = $this->getEmbedding($query);
        foreach ($this->cache as $item) {
            if ($this->similarity($queryEmbedding, $item['embedding']) > $this->threshold) {
                return $item['response'];
            }
        }
        return null;
    }
}
```

### Exercise 3: Multi-language Search

Build cross-language search capabilities:

```php
function crossLanguageSearch(string $query, array $documents): array
{
    // Mistral embeddings work across languages
    // Search French documents with English queries
}
```

## Troubleshooting

### Issue: High Embedding Costs

- **Solution**: Batch multiple texts in single requests
- Cache embeddings for frequently used content
- Use smaller text chunks when possible

### Issue: Poor Search Results

- **Solution**: Preprocess text (remove special characters, normalize)
- Experiment with different similarity thresholds
- Consider hybrid search (combine with keyword matching)

### Issue: Memory Usage with Large Datasets

- **Solution**: Use vector databases (Pinecone, Weaviate, Qdrant)
- Implement pagination for large result sets
- Store embeddings separately from documents

### Issue: Similarity Scores Not Meaningful

- **Solution**: Normalize embeddings before comparison
- Use domain-specific thresholds
- Consider different similarity metrics

## Next Steps

Continue your journey with:

1. **[07-ocr](../07-ocr)**: Process documents with OCR
2. **[08-audio](../08-audio)**: Transcribe audio content
3. **[05-function-calling](../05-function-calling)**: Combine embeddings with functions

### Further Reading

- [Mistral Embeddings Documentation](https://docs.mistral.ai/capabilities/embeddings)
- [Vector Database Comparison](https://github.com/topics/vector-database)
- [Information Retrieval Concepts](https://www.manning.com/books/introduction-to-information-retrieval)

### Advanced Applications

- **RAG Systems**: Retrieval-Augmented Generation
- **Semantic Memory**: Long-term context storage for chatbots
- **Anomaly Detection**: Find outliers in text data
- **Topic Modeling**: Discover themes in document collections
- **Personalization**: User preference modeling

Remember: Embeddings are the foundation of modern semantic AI applications. They enable computers to understand meaning,
not just match keywords!
