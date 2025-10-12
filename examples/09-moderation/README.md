# Content Moderation with Mistral PHP SDK

## Overview

This example demonstrates Mistral's content moderation capabilities for identifying and filtering inappropriate content.
The moderation API helps ensure user-generated content meets community guidelines, complies with regulations, and
maintains a safe environment for all users.

### Real-world Use Cases

- Social media content filtering
- Forum and comment moderation
- Chat application safety
- User-generated content review
- Compliance with content policies
- Child safety protection

### Prerequisites

- Completed [08-audio](../08-audio) example
- Understanding of content safety concepts
- Knowledge of moderation categories
- Familiarity with threshold tuning

## Concepts

### Moderation Categories

Mistral's moderation model detects:

- **Sexual Content**: Explicit or suggestive material
- **Hate Speech**: Discriminatory or offensive language
- **Violence**: Threats, graphic content
- **Self-Harm**: Content promoting dangerous behavior
- **Illegal Activities**: Criminal content
- **PII**: Personal Identifiable Information

### Confidence Scores

Each category returns a confidence score (0-1):

- **0.0-0.3**: Low confidence (likely safe)
- **0.3-0.7**: Medium confidence (review recommended)
- **0.7-1.0**: High confidence (likely violates policy)

### Moderation Strategies

- **Automatic Filtering**: Block high-confidence violations
- **Human Review Queue**: Flag medium-confidence content
- **Context-Aware**: Consider conversation context
- **Multi-Language**: Support for various languages

## Implementation

### Basic Content Moderation

Check content for policy violations:

```php
<?php

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Moderations\ModerationRequest;

$mistral = new Mistral($_ENV['MISTRAL_API_KEY']);

// Content to moderate
$content = "This is the text to check for inappropriate content.";

// Create moderation request
$request = ModerationRequest::from([
    'model' => 'mistral-moderation-latest',
    'input' => $content,
]);

$response = $mistral->moderations()->create($request);

// Check results
foreach ($response->results as $result) {
    if ($result->flagged) {
        echo "Content flagged for moderation!\n";
        echo "Categories violated:\n";

        foreach ($result->categories as $category => $violated) {
            if ($violated) {
                $score = $result->categoryScores[$category];
                echo "  - {$category}: " . round($score * 100, 2) . "%\n";
            }
        }
    } else {
        echo "Content passed moderation.\n";
    }
}
```

### Advanced Moderation System

Build a comprehensive content moderation system:

```php
class ContentModerator
{
    private Mistral $client;
    private array $thresholds;
    private array $customFilters = [];

    public function __construct(string $apiKey, array $thresholds = [])
    {
        $this->client = new Mistral($apiKey);

        // Default thresholds for each category
        $this->thresholds = array_merge([
            'sexual' => 0.7,
            'hate' => 0.6,
            'violence' => 0.7,
            'self_harm' => 0.8,
            'illegal' => 0.7,
            'pii' => 0.9,
        ], $thresholds);
    }

    public function moderate(string $content, array $context = []): array
    {
        // Pre-filter with custom rules
        $customViolations = $this->applyCustomFilters($content);
        if (!empty($customViolations)) {
            return [
                'action' => 'block',
                'reason' => 'custom_filter',
                'violations' => $customViolations,
                'confidence' => 1.0,
            ];
        }

        // Check for PII before sending to API
        $piiCheck = $this->checkPII($content);
        if ($piiCheck['found']) {
            return [
                'action' => 'block',
                'reason' => 'pii_detected',
                'violations' => ['pii' => $piiCheck['types']],
                'confidence' => 1.0,
            ];
        }

        // Send to Mistral moderation API
        $request = ModerationRequest::from([
            'model' => 'mistral-moderation-latest',
            'input' => $content,
        ]);

        try {
            $response = $this->client->moderations()->create($request);
            return $this->analyzeResponse($response, $context);
        } catch (Exception $e) {
            // Fallback to conservative approach on error
            return [
                'action' => 'review',
                'reason' => 'moderation_error',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function analyzeResponse($response, array $context): array
    {
        $result = $response->results[0];
        $action = 'approve';
        $violations = [];
        $maxScore = 0;

        foreach ($result->categoryScores as $category => $score) {
            if ($score > $this->thresholds[$category]) {
                $violations[$category] = $score;
                $maxScore = max($maxScore, $score);

                // Determine action based on score
                if ($score > 0.9) {
                    $action = 'block';
                } elseif ($score > 0.7 && $action !== 'block') {
                    $action = 'review';
                }
            }
        }

        // Consider context
        if (!empty($context['user_history'])) {
            $action = $this->adjustForUserHistory($action, $context['user_history']);
        }

        return [
            'action' => $action,
            'flagged' => $result->flagged,
            'violations' => $violations,
            'confidence' => $maxScore,
            'categories' => $result->categories,
            'scores' => $result->categoryScores,
        ];
    }

    private function checkPII(string $content): array
    {
        $patterns = [
            'email' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
            'phone' => '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
            'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
            'credit_card' => '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/',
        ];

        $found = [];
        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $content)) {
                $found[] = $type;
            }
        }

        return [
            'found' => !empty($found),
            'types' => $found,
        ];
    }

    private function applyCustomFilters(string $content): array
    {
        $violations = [];

        foreach ($this->customFilters as $filter) {
            if ($filter['type'] === 'keyword' &&
                stripos($content, $filter['value']) !== false) {
                $violations[] = $filter['name'];
            } elseif ($filter['type'] === 'regex' &&
                preg_match($filter['value'], $content)) {
                $violations[] = $filter['name'];
            }
        }

        return $violations;
    }

    private function adjustForUserHistory(string $action, array $history): string
    {
        // Stricter moderation for repeat offenders
        if ($history['violations'] > 3) {
            return $action === 'review' ? 'block' : $action;
        }

        // More lenient for trusted users
        if ($history['trust_score'] > 0.9 && $action === 'review') {
            return 'approve';
        }

        return $action;
    }

    public function addCustomFilter(string $name, string $type, string $value): void
    {
        $this->customFilters[] = [
            'name' => $name,
            'type' => $type,
            'value' => $value,
        ];
    }

    public function moderateBatch(array $contents): array
    {
        $results = [];

        foreach ($contents as $id => $content) {
            $results[$id] = $this->moderate($content);
        }

        return $results;
    }
}
```

### Real-time Chat Moderation

Implement live chat filtering:

```php
class ChatModerator
{
    private ContentModerator $moderator;
    private array $messageHistory = [];
    private int $contextWindow = 5;

    public function __construct(ContentModerator $moderator)
    {
        $this->moderator = $moderator;
    }

    public function moderateMessage(
        string $userId,
        string $message,
        string $channelId
    ): array {
        // Get conversation context
        $context = $this->getContext($channelId);

        // Check for patterns across messages
        $patternViolations = $this->checkPatterns($userId, $message);

        // Moderate the message
        $result = $this->moderator->moderate($message, [
            'user_id' => $userId,
            'channel_id' => $channelId,
            'context' => $context,
            'pattern_violations' => $patternViolations,
        ]);

        // Store in history
        $this->addToHistory($channelId, $userId, $message, $result);

        // Apply real-time actions
        return $this->applyActions($result, $userId, $channelId);
    }

    private function checkPatterns(string $userId, string $message): array
    {
        $violations = [];

        // Check for spam patterns
        if ($this->isSpam($userId, $message)) {
            $violations[] = 'spam';
        }

        // Check for flooding
        if ($this->isFlooding($userId)) {
            $violations[] = 'flooding';
        }

        // Check for evasion techniques
        if ($this->hasEvasionTechniques($message)) {
            $violations[] = 'evasion';
        }

        return $violations;
    }

    private function isSpam(string $userId, string $message): bool
    {
        // Check for repeated messages
        $userMessages = $this->messageHistory[$userId] ?? [];
        $similar = 0;

        foreach ($userMessages as $historical) {
            similar_text($message, $historical['message'], $percent);
            if ($percent > 80) {
                $similar++;
            }
        }

        return $similar > 2;
    }

    private function isFlooding(string $userId): bool
    {
        $userMessages = $this->messageHistory[$userId] ?? [];
        $recentMessages = array_filter($userMessages, function($msg) {
            return (time() - $msg['timestamp']) < 10; // Last 10 seconds
        });

        return count($recentMessages) > 5;
    }

    private function hasEvasionTechniques(string $message): bool
    {
        // Check for character substitution (l33t speak)
        $patterns = [
            '/[0o]\s*[Nn]\s*[1l]\s*[Yy]/', // 0N1Y
            '/[@]\s*[Ss]\s*[\$]/', // @S$
            '/[1!]\s*[0o]\s*[Vv]\s*[3e]/', // L0V3
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    private function applyActions(array $result, string $userId, string $channelId): array
    {
        switch ($result['action']) {
            case 'block':
                return [
                    'allow' => false,
                    'action' => 'block_message',
                    'notification' => 'Your message was blocked for violating community guidelines.',
                    'log' => true,
                ];

            case 'review':
                return [
                    'allow' => true,
                    'action' => 'flag_for_review',
                    'notification' => null,
                    'shadow_ban' => false,
                    'log' => true,
                ];

            case 'approve':
                return [
                    'allow' => true,
                    'action' => 'approve',
                    'notification' => null,
                    'log' => false,
                ];

            default:
                return [
                    'allow' => true,
                    'action' => 'default',
                    'notification' => null,
                    'log' => false,
                ];
        }
    }

    private function getContext(string $channelId): array
    {
        return array_slice(
            $this->messageHistory[$channelId] ?? [],
            -$this->contextWindow
        );
    }

    private function addToHistory(
        string $channelId,
        string $userId,
        string $message,
        array $result
    ): void {
        if (!isset($this->messageHistory[$channelId])) {
            $this->messageHistory[$channelId] = [];
        }

        if (!isset($this->messageHistory[$userId])) {
            $this->messageHistory[$userId] = [];
        }

        $entry = [
            'message' => $message,
            'timestamp' => time(),
            'result' => $result,
        ];

        $this->messageHistory[$channelId][] = $entry;
        $this->messageHistory[$userId][] = $entry;

        // Limit history size
        $this->messageHistory[$channelId] = array_slice(
            $this->messageHistory[$channelId],
            -100
        );
    }
}
```

## Code Example

Complete working example (`moderation.php`):

```php
<?php

require_once 'vendor/autoload.php';

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Moderations\ModerationRequest;

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');
if (!$apiKey) {
    die("Please set your MISTRAL_API_KEY environment variable\n");
}

$mistral = new Mistral($apiKey);

// Example 1: Basic Moderation
echo "=== Example 1: Basic Content Check ===\n\n";

$testContents = [
    "This is a completely normal message about programming.",
    "I hate certain groups of people!", // Hate speech example
    "Check out my website: example@email.com call 555-1234", // PII example
    "Let's discuss the latest technology trends peacefully.",
];

foreach ($testContents as $content) {
    echo "Checking: \"" . substr($content, 0, 50) . "...\"\n";

    $request = ModerationRequest::from([
        'model' => 'mistral-moderation-latest',
        'input' => $content,
    ]);

    try {
        $response = $mistral->moderations()->create($request);
        $result = $response->results[0];

        if ($result->flagged) {
            echo "  ❌ FLAGGED - Categories: ";
            $flaggedCategories = array_keys(array_filter($result->categories));
            echo implode(', ', $flaggedCategories) . "\n";

            // Show confidence scores
            foreach ($flaggedCategories as $category) {
                $score = $result->categoryScores[$category];
                echo "    - {$category}: " . round($score * 100, 1) . "% confidence\n";
            }
        } else {
            echo "  ✓ APPROVED - Content is safe\n";
        }
    } catch (Exception $e) {
        echo "  ⚠ Error: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

// Example 2: Threshold-based Moderation
echo "=== Example 2: Custom Threshold Moderation ===\n\n";

class ThresholdModerator
{
    private Mistral $client;
    private array $thresholds;

    public function __construct(Mistral $client, array $thresholds)
    {
        $this->client = $client;
        $this->thresholds = $thresholds;
    }

    public function check(string $content): array
    {
        $request = ModerationRequest::from([
            'model' => 'mistral-moderation-latest',
            'input' => $content,
        ]);

        $response = $this->client->moderations()->create($request);
        $result = $response->results[0];

        $violations = [];
        foreach ($result->categoryScores as $category => $score) {
            $threshold = $this->thresholds[$category] ?? 0.5;
            if ($score > $threshold) {
                $violations[$category] = [
                    'score' => $score,
                    'threshold' => $threshold,
                    'severity' => $this->getSeverity($score),
                ];
            }
        }

        return [
            'safe' => empty($violations),
            'violations' => $violations,
            'action' => $this->determineAction($violations),
        ];
    }

    private function getSeverity(float $score): string
    {
        if ($score > 0.9) return 'critical';
        if ($score > 0.7) return 'high';
        if ($score > 0.5) return 'medium';
        return 'low';
    }

    private function determineAction(array $violations): string
    {
        if (empty($violations)) return 'approve';

        $maxSeverity = 'low';
        foreach ($violations as $violation) {
            if ($violation['severity'] === 'critical') return 'block';
            if ($violation['severity'] === 'high') $maxSeverity = 'high';
            elseif ($violation['severity'] === 'medium' && $maxSeverity === 'low') {
                $maxSeverity = 'medium';
            }
        }

        return $maxSeverity === 'high' ? 'review' : 'warn';
    }
}

$moderator = new ThresholdModerator($mistral, [
    'sexual' => 0.6,
    'hate' => 0.5,
    'violence' => 0.7,
    'self_harm' => 0.8,
]);

$testContent = "This message contains potentially inappropriate content.";
$result = $moderator->check($testContent);

echo "Content: \"{$testContent}\"\n";
echo "Safe: " . ($result['safe'] ? 'Yes' : 'No') . "\n";
echo "Action: {$result['action']}\n";

if (!empty($result['violations'])) {
    echo "Violations:\n";
    foreach ($result['violations'] as $category => $details) {
        echo "  - {$category}: {$details['severity']} ";
        echo "(" . round($details['score'] * 100, 1) . "% > ";
        echo round($details['threshold'] * 100, 1) . "%)\n";
    }
}

echo "\n";

// Example 3: Batch Moderation
echo "=== Example 3: Batch Content Processing ===\n\n";

$userComments = [
    'user1' => "Great article! Thanks for sharing.",
    'user2' => "I disagree with your opinion but respect it.",
    'user3' => "This is spam! Buy products at spam-site.com",
    'user4' => "Interesting perspective on the topic.",
];

echo "Moderating " . count($userComments) . " user comments:\n\n";

$results = [];
foreach ($userComments as $userId => $comment) {
    $request = ModerationRequest::from([
        'model' => 'mistral-moderation-latest',
        'input' => $comment,
    ]);

    $response = $mistral->moderations()->create($request);
    $results[$userId] = [
        'comment' => $comment,
        'flagged' => $response->results[0]->flagged,
        'scores' => $response->results[0]->categoryScores,
    ];
}

// Summary
$flaggedCount = count(array_filter($results, fn($r) => $r['flagged']));
echo "Summary:\n";
echo "  Total comments: " . count($results) . "\n";
echo "  Flagged: {$flaggedCount}\n";
echo "  Approved: " . (count($results) - $flaggedCount) . "\n\n";

// Details
foreach ($results as $userId => $result) {
    $status = $result['flagged'] ? '❌ Flagged' : '✓ OK';
    echo "{$userId}: {$status}\n";
    echo "  Comment: \"" . substr($result['comment'], 0, 50) . "...\"\n";

    if ($result['flagged']) {
        $topCategory = array_keys($result['scores'], max($result['scores']))[0];
        echo "  Main issue: {$topCategory} ";
        echo "(" . round($result['scores'][$topCategory] * 100, 1) . "%)\n";
    }
    echo "\n";
}

// Example 4: Context-Aware Moderation
echo "=== Example 4: Context-Aware Moderation ===\n\n";

function moderateWithContext(
    Mistral $client,
    string $message,
    array $previousMessages = []
): array {
    // Build context string
    $context = '';
    if (!empty($previousMessages)) {
        $context = "Previous messages:\n";
        foreach ($previousMessages as $prev) {
            $context .= "- {$prev}\n";
        }
        $context .= "\nCurrent message:\n";
    }

    $fullContent = $context . $message;

    $request = ModerationRequest::from([
        'model' => 'mistral-moderation-latest',
        'input' => $fullContent,
    ]);

    $response = $client->moderations()->create($request);
    $result = $response->results[0];

    return [
        'message' => $message,
        'flagged' => $result->flagged,
        'context_considered' => !empty($previousMessages),
        'categories' => array_keys(array_filter($result->categories)),
    ];
}

// Simulate a conversation
$conversation = [
    "I'm having a really tough day",
    "Everything feels overwhelming",
    "I don't know what to do anymore",
];

echo "Analyzing conversation context:\n\n";

for ($i = 0; $i < count($conversation); $i++) {
    $previousMessages = array_slice($conversation, 0, $i);
    $currentMessage = $conversation[$i];

    $result = moderateWithContext($mistral, $currentMessage, $previousMessages);

    echo "Message {$i}: \"{$currentMessage}\"\n";
    echo "  Context messages: " . count($previousMessages) . "\n";
    echo "  Flagged: " . ($result['flagged'] ? 'Yes' : 'No') . "\n";

    if ($result['flagged']) {
        echo "  Categories: " . implode(', ', $result['categories']) . "\n";
    }
    echo "\n";
}

// Example 5: Multi-language Moderation
echo "=== Example 5: Multi-language Support ===\n\n";

$multilingualContent = [
    'en' => "This is inappropriate content",
    'fr' => "Ceci est un contenu inapproprié",
    'es' => "Este es contenido inapropiado",
    'de' => "Dies ist unangemessener Inhalt",
];

echo "Testing moderation across languages:\n\n";

foreach ($multilingualContent as $lang => $content) {
    $request = ModerationRequest::from([
        'model' => 'mistral-moderation-latest',
        'input' => $content,
    ]);

    $response = $mistral->moderations()->create($request);
    $result = $response->results[0];

    echo "Language: {$lang}\n";
    echo "  Content: \"{$content}\"\n";
    echo "  Flagged: " . ($result->flagged ? 'Yes' : 'No') . "\n";

    if ($result->flagged) {
        $categories = array_keys(array_filter($result->categories));
        echo "  Categories: " . implode(', ', $categories) . "\n";
    }
    echo "\n";
}

// Example 6: Moderation Analytics
echo "=== Example 6: Moderation Analytics ===\n\n";

class ModerationAnalytics
{
    private array $stats = [
        'total' => 0,
        'flagged' => 0,
        'categories' => [],
        'severity_distribution' => [],
    ];

    public function addResult(array $result): void
    {
        $this->stats['total']++;

        if ($result['flagged']) {
            $this->stats['flagged']++;

            foreach ($result['categories'] as $category => $flagged) {
                if ($flagged) {
                    $this->stats['categories'][$category] =
                        ($this->stats['categories'][$category] ?? 0) + 1;
                }
            }
        }
    }

    public function getReport(): array
    {
        $flagRate = $this->stats['total'] > 0
            ? ($this->stats['flagged'] / $this->stats['total']) * 100
            : 0;

        return [
            'total_checked' => $this->stats['total'],
            'total_flagged' => $this->stats['flagged'],
            'flag_rate' => round($flagRate, 2) . '%',
            'top_violations' => $this->getTopViolations(),
        ];
    }

    private function getTopViolations(): array
    {
        arsort($this->stats['categories']);
        return array_slice($this->stats['categories'], 0, 3, true);
    }
}

$analytics = new ModerationAnalytics();

// Simulate processing multiple contents
$sampleContents = [
    "Normal content",
    "Another normal message",
    "Potentially problematic content",
    "Safe discussion",
];

foreach ($sampleContents as $content) {
    $request = ModerationRequest::from([
        'model' => 'mistral-moderation-latest',
        'input' => $content,
    ]);

    $response = $mistral->moderations()->create($request);
    $analytics->addResult($response->results[0]);
}

$report = $analytics->getReport();
echo "Moderation Analytics Report:\n";
echo "  Total content checked: {$report['total_checked']}\n";
echo "  Total flagged: {$report['total_flagged']}\n";
echo "  Flag rate: {$report['flag_rate']}\n";

if (!empty($report['top_violations'])) {
    echo "  Top violation categories:\n";
    foreach ($report['top_violations'] as $category => $count) {
        echo "    - {$category}: {$count} occurrences\n";
    }
}

echo "\n=== Summary ===\n";
echo "Content moderation capabilities:\n";
echo "1. Real-time content filtering\n";
echo "2. Multi-category violation detection\n";
echo "3. Customizable thresholds\n";
echo "4. Context-aware moderation\n";
echo "5. Multi-language support\n";
echo "6. Batch processing\n";
echo "7. Analytics and reporting\n";
```

## Expected Output

```
=== Example 1: Basic Content Check ===

Checking: "This is a completely normal message about progra..."
  ✓ APPROVED - Content is safe

Checking: "I hate certain groups of people!..."
  ❌ FLAGGED - Categories: hate
    - hate: 85.3% confidence

Checking: "Check out my website: example@email.com call 555..."
  ❌ FLAGGED - Categories: pii
    - pii: 92.1% confidence

=== Example 2: Custom Threshold Moderation ===

Content: "This message contains potentially inappropriate content."
Safe: No
Action: review
Violations:
  - inappropriate: medium (65.2% > 50.0%)

[Additional examples follow...]
```

## Try It Yourself

### Exercise 1: Build a Comment Filter

Create a comprehensive comment moderation system:

```php
class CommentFilter {
    public function filterComment(string $comment, array $userProfile): array
    {
        $checks = [
            'moderation' => $this->checkModeration($comment),
            'spam' => $this->checkSpam($comment),
            'links' => $this->checkLinks($comment),
            'reputation' => $this->checkUserReputation($userProfile),
        ];

        return $this->determineAction($checks);
    }
}
```

### Exercise 2: Implement Shadow Banning

Silently hide problematic content:

```php
class ShadowBanManager {
    public function shouldShadowBan(string $userId, array $violation): bool
    {
        // Implement logic for shadow banning repeat offenders
        // Content appears posted to them but hidden from others
    }
}
```

### Exercise 3: Create Moderation Queue

Build a review system for flagged content:

```php
class ModerationQueue {
    public function addToQueue(array $content, string $priority): void
    {
        // Add flagged content to review queue
    }

    public function getNextForReview(): ?array
    {
        // Get highest priority item for human review
    }
}
```

## Troubleshooting

### Issue: False Positives

- **Solution**: Adjust thresholds based on your use case
- Implement whitelisting for known safe terms
- Consider context and user history

### Issue: Evasion Techniques

- **Solution**: Normalize text before moderation
- Check for character substitution
- Implement pattern matching for common evasions

### Issue: Performance with High Volume

- **Solution**: Batch process where possible
- Implement caching for repeated content
- Use async processing for non-critical checks

### Issue: Multi-language Accuracy

- **Solution**: Specify language when known
- Use language detection first
- Adjust thresholds per language

## Next Steps

Continue your journey with:

1. **[10-error-handling](../10-error-handling)**: Handle moderation errors gracefully
2. **[05-function-calling](../05-function-calling)**: Automate moderation actions
3. **[06-embeddings](../06-embeddings)**: Detect similar inappropriate content

### Further Reading

- [Mistral Moderation Documentation](https://docs.mistral.ai/capabilities/moderation)
- [Content Moderation Best Practices](https://docs.mistral.ai/guides/moderation)
- [Online Safety Guidelines](https://www.esafety.gov.au/)

### Advanced Applications

- **Adaptive Filtering**: Learn from moderation decisions
- **Cross-platform Moderation**: Unified moderation across channels
- **Sentiment Analysis**: Combine with emotion detection
- **Escalation Systems**: Automatic escalation for serious violations
- **Compliance Reporting**: Generate regulatory compliance reports

Remember: Effective moderation balances safety with user experience. Always provide clear guidelines and appeal
processes!
