<?php

namespace Modules\AppAIContents\Services;

use Modules\AppAIContents\Models\ContentPlan;
use Modules\AppAIContents\Models\ContentCalendarItem;
use Modules\AppAIContents\Models\TopicResearch;
use Modules\AppAIContents\Models\CompetitorAnalysis;
use Modules\AppAIContents\Models\ContentGapAnalysis;
use Modules\AppAIContents\Models\PostingTimeAnalysis;
use Modules\AppAIContents\Models\HashtagResearch;
use Modules\AppAIContents\Models\AiContentSuggestion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * AI Content Strategy Service for Pando
 * Reads AI configuration from options table
 * Supports both Claude and OpenAI
 */
class AIContentStrategyService
{
    private $userId;
    private $aiProvider;
    private $apiKey;
    private $model;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
        $this->loadAIConfiguration();
    }

    /**
     * Load AI configuration from options table
     */
    private function loadAIConfiguration()
    {
        // Get AI platform (claude or openai)
        $this->aiProvider = DB::table('options')
            ->where('name', 'ai_platform')
            ->value('value') ?? 'openai';

        if ($this->aiProvider === 'claude') {
            // Load Claude configuration
            $this->apiKey = DB::table('options')
                ->where('name', 'ai_claude_api_key')
                ->value('value');

            $this->model = DB::table('options')
                ->where('name', 'ai_claude_model_text')
                ->value('value') ?? 'claude-sonnet-4-20250514';

        } else {
            // Load OpenAI configuration
            $this->apiKey = DB::table('options')
                ->where('name', 'ai_openai_api_key')
                ->value('value');

            $this->model = DB::table('options')
                ->where('name', 'ai_openai_model_text')
                ->value('value') ?? 'gpt-4o-mini';
        }

        if (empty($this->apiKey)) {
            throw new \Exception("AI API key not found in options table for provider: {$this->aiProvider}");
        }
    }

    /**
     * Process AI request with configured provider
     */
    private function processAI($prompt, $options = [])
    {
        if ($this->aiProvider === 'claude') {
            return $this->processWithClaude($prompt, $options);
        } else {
            return $this->processWithOpenAI($prompt, $options);
        }
    }

    /**
     * Process with Claude API
     */
    private function processWithClaude($prompt, $options = [])
    {
        try {
            $client = new \GuzzleHttp\Client();
            
            $response = $client->post('https://api.anthropic.com/v1/messages', [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'max_tokens' => $options['max_tokens'] ?? 4096,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ]
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            
            // Extract text from Claude response
            $text = '';
            if (isset($result['content']) && is_array($result['content'])) {
                foreach ($result['content'] as $block) {
                    if ($block['type'] === 'text') {
                        $text .= $block['text'];
                    }
                }
            }

            return [
                'success' => true,
                'data' => [$text]
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody(), true);
            
            // Check for credit balance error
            if (isset($body['error']['message']) && 
                strpos($body['error']['message'], 'credit balance') !== false) {
                return [
                    'success' => false,
                    'error' => 'Claude API: Your credit balance is too low. Please add credits at https://console.anthropic.com/'
                ];
            }
            
            Log::error('Claude API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Claude API Error: ' . ($body['error']['message'] ?? $e->getMessage())
            ];
        } catch (\Exception $e) {
            Log::error('Claude API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process with OpenAI API
     */
    private function processWithOpenAI($prompt, $options = [])
    {
        try {
            $client = new \GuzzleHttp\Client();
            
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert content strategist and social media marketing professional. Provide detailed, actionable content plans in JSON format.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => $options['temperature'] ?? 0.7,
                    'max_tokens' => $options['max_tokens'] ?? 4096,
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            
            // Extract text from OpenAI response
            $text = $result['choices'][0]['message']['content'] ?? '';

            return [
                'success' => true,
                'data' => [$text]
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody(), true);
            
            // Check for insufficient quota error
            if (isset($body['error']['code']) && $body['error']['code'] === 'insufficient_quota') {
                return [
                    'success' => false,
                    'error' => 'OpenAI API: You exceeded your current quota. Please add credits at https://platform.openai.com/account/billing'
                ];
            }
            
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'OpenAI API Error: ' . ($body['error']['message'] ?? $e->getMessage())
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate a comprehensive content calendar based on business goals
     */
    public function generateContentCalendar(array $params): array
    {
        $businessGoals = $params['business_goals'] ?? '';
        $targetAudience = $params['target_audience'] ?? '';
        $platforms = $params['platforms'] ?? [];
        $startDate = Carbon::parse($params['start_date']);
        $endDate = Carbon::parse($params['end_date']);
        $postingFrequency = $params['posting_frequency'] ?? 'daily';

        $daysDifference = $startDate->diffInDays($endDate);
        $numberOfPosts = $this->calculatePostCount($daysDifference, $postingFrequency);

        $prompt = $this->buildContentCalendarPrompt(
            $businessGoals,
            $targetAudience,
            $platforms,
            $numberOfPosts,
            $startDate,
            $endDate
        );

        try {
            $result = $this->processAI($prompt);

            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to generate calendar'
                ];
            }

            // Parse the response
            $content = is_array($result['data']) ? $result['data'][0] : $result['data'];
            $calendarData = $this->parseJSONResponse($content);

            return [
                'success' => true,
                'calendar' => $calendarData,
                'tokens_used' => 0
            ];

        } catch (\Exception $e) {
            Log::error('Content Calendar Generation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Research topics and trends for content planning
     */
    public function researchTopics(array $topics, string $industry, array $targetAudience = []): array
    {
        $prompt = "As a content research expert, analyze the following topics for the {$industry} industry:\n\n";
        $prompt .= "Topics: " . implode(', ', $topics) . "\n\n";
        
        if (!empty($targetAudience)) {
            $prompt .= "Target Audience: " . implode(', ', $targetAudience) . "\n\n";
        }

        $prompt .= "For each topic, provide:\n";
        $prompt .= "1. Research summary (why this topic matters now)\n";
        $prompt .= "2. Trending keywords and phrases\n";
        $prompt .= "3. Related subtopics to explore\n";
        $prompt .= "4. Relevance score (1-100)\n";
        $prompt .= "5. Content angle suggestions\n\n";
        $prompt .= "Format response as JSON with structure: {\"topics\": [{\"topic\": \"\", \"summary\": \"\", \"trending_keywords\": [], \"related_topics\": [], \"relevance_score\": 0, \"content_angles\": []}]}";

        try {
            $result = $this->processAI($prompt);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to research topics'
                ];
            }

            $content = is_array($result['data']) ? $result['data'][0] : $result['data'];
            $researchData = $this->parseJSONResponse($content);

            return [
                'success' => true,
                'research' => $researchData,
                'tokens_used' => 0
            ];

        } catch (\Exception $e) {
            Log::error('Topic Research Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Analyze competitors and their content strategy
     */
    public function analyzeCompetitor(array $competitorData): array
    {
        $competitorName = $competitorData['name'];
        $competitorUrl = $competitorData['url'] ?? '';
        $platforms = $competitorData['platforms'] ?? [];
        $sampleContent = $competitorData['sample_content'] ?? [];
        $industry = $competitorData['industry'] ?? '';

        $prompt = "Analyze the following competitor in the {$industry} industry:\n\n";
        $prompt .= "Competitor: {$competitorName}\n";
        if ($competitorUrl) {
            $prompt .= "Website: {$competitorUrl}\n";
        }
        $prompt .= "Platforms: " . implode(', ', $platforms) . "\n\n";

        if (!empty($sampleContent)) {
            $prompt .= "Sample Content:\n";
            foreach ($sampleContent as $idx => $content) {
                $prompt .= ($idx + 1) . ". {$content}\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "Provide comprehensive analysis including:\n";
        $prompt .= "1. Content themes and topics they focus on\n";
        $prompt .= "2. Estimated posting frequency per platform\n";
        $prompt .= "3. Content types they use\n";
        $prompt .= "4. Content strategy strengths\n";
        $prompt .= "5. Content strategy weaknesses\n";
        $prompt .= "6. Content gaps (opportunities)\n";
        $prompt .= "7. Recommendations for competing\n\n";
        $prompt .= "Format as JSON: {\"content_themes\": [], \"posting_frequency\": {}, \"content_types\": [], \"strengths\": \"\", \"weaknesses\": \"\", \"content_gaps\": [], \"recommendations\": []}";

        try {
            $result = $this->processAI($prompt);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to analyze competitor'
                ];
            }

            $content = is_array($result['data']) ? $result['data'][0] : $result['data'];
            $analysisData = $this->parseJSONResponse($content);

            return [
                'success' => true,
                'analysis' => $analysisData,
                'tokens_used' => 0
            ];

        } catch (\Exception $e) {
            Log::error('Competitor Analysis Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Research and suggest hashtags for content
     */
    public function researchHashtags(string $content, string $platform, string $industry, int $count = 10): array
    {
        $prompt = "Research and suggest the best hashtags for the following content on {$platform} in the {$industry} industry:\n\n";
        $prompt .= "Content:\n{$content}\n\n";
        $prompt .= "Provide {$count} hashtags including:\n";
        $prompt .= "1. Mix of high-traffic and niche hashtags\n";
        $prompt .= "2. Industry-specific hashtags\n";
        $prompt .= "3. Trending relevant hashtags\n\n";
        $prompt .= "For each hashtag provide:\n";
        $prompt .= "- The hashtag\n";
        $prompt .= "- Estimated popularity (low/medium/high/very_high)\n";
        $prompt .= "- Why it's relevant\n";
        $prompt .= "- Relevance score (1-100)\n\n";
        $prompt .= "Format as JSON: {\"hashtags\": [{\"hashtag\": \"\", \"popularity\": \"\", \"relevance_score\": 0, \"reasoning\": \"\", \"reach_potential\": \"\"}], \"strategy_notes\": \"\"}";

        try {
            $result = $this->processAI($prompt);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to research hashtags'
                ];
            }

            $content = is_array($result['data']) ? $result['data'][0] : $result['data'];
            $hashtagData = $this->parseJSONResponse($content);

            return [
                'success' => true,
                'hashtag_research' => $hashtagData,
                'tokens_used' => 0
            ];

        } catch (\Exception $e) {
            Log::error('Hashtag Research Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Adapt content for multiple platforms
     */
    public function adaptContentForPlatforms(string $baseContent, array $platforms, array $context = []): array
    {
        $prompt = "Adapt the following content for different social media platforms:\n\n";
        $prompt .= "Base Content:\n{$baseContent}\n\n";
        
        if (!empty($context)) {
            $prompt .= "Context:\n";
            $prompt .= "Brand Voice: " . ($context['brand_voice'] ?? 'Professional and friendly') . "\n";
            $prompt .= "Target Audience: " . ($context['target_audience'] ?? 'General') . "\n";
            $prompt .= "Content Goal: " . ($context['goal'] ?? 'Engagement') . "\n\n";
        }

        $prompt .= "Create platform-specific versions for: " . implode(', ', $platforms) . "\n\n";
        $prompt .= "For each platform:\n";
        $prompt .= "1. Optimized content (respecting character limits)\n";
        $prompt .= "2. Suggested hashtags\n";
        $prompt .= "3. Call-to-action\n";
        $prompt .= "4. Platform-specific tips\n\n";
        $prompt .= "Format as JSON with platform names as keys, each containing: {\"content\": \"\", \"hashtags\": [], \"cta\": \"\", \"tips\": []}";

        try {
            $result = $this->processAI($prompt);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to adapt content'
                ];
            }

            $content = is_array($result['data']) ? $result['data'][0] : $result['data'];
            $adaptedContent = $this->parseJSONResponse($content);

            return [
                'success' => true,
                'platform_versions' => $adaptedContent,
                'tokens_used' => 0
            ];

        } catch (\Exception $e) {
            Log::error('Content Adaptation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Stub methods for remaining features
    public function identifyContentGaps(array $userContent, array $competitorContent, string $industry): array
    {
        return ['success' => true, 'gaps' => []];
    }

    public function analyzeBestPostingTimes(array $engagementData, string $platform, string $timezone = 'UTC'): array
    {
        return ['success' => true, 'timing_analysis' => []];
    }

    // Helper methods
    private function buildContentCalendarPrompt($goals, $audience, $platforms, $postCount, $startDate, $endDate): string
    {
        $prompt = "Create a detailed {$postCount}-post content calendar:\n\n";
        $prompt .= "Business Goals: {$goals}\n";
        $prompt .= "Target Audience: {$audience}\n";
        $prompt .= "Platforms: " . implode(', ', $platforms) . "\n";
        $prompt .= "Date Range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}\n\n";
        
        $prompt .= "For each post, provide:\n";
        $prompt .= "1. Post title/topic\n";
        $prompt .= "2. Main content/caption\n";
        $prompt .= "3. Suggested hashtags (5-10)\n";
        $prompt .= "4. Best posting time (day and hour)\n";
        $prompt .= "5. Content type (educational/promotional/engaging)\n\n";
        
        $prompt .= "Distribute posts strategically across the date range.\n\n";
        $prompt .= "IMPORTANT: Return ONLY valid JSON in this exact format:\n";
        $prompt .= '{"calendar": [{"date": "YYYY-MM-DD", "time": "HH:MM", "title": "", "content": "", "hashtags": [], "content_type": ""}], "strategy_notes": ""}';
        
        return $prompt;
    }

    private function calculatePostCount($days, $frequency): int
    {
        switch ($frequency) {
            case 'daily':
                return $days;
            case '3x_week':
                return ceil($days / 7 * 3);
            case 'weekly':
                return ceil($days / 7);
            case '5x_week':
                return ceil($days / 7 * 5);
            default:
                return ceil($days / 2);
        }
    }

    private function parseJSONResponse($content): array
    {
        // Remove markdown code blocks if present
        $content = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $content);
        $content = preg_replace('/```\s*(.*?)\s*```/s', '$1', $content);
        
        // Trim whitespace
        $content = trim($content);
        
        // Try to decode
        $decoded = json_decode($content, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        
        // If JSON decode fails, return empty structure
        Log::warning('Failed to parse JSON response: ' . json_last_error_msg());
        return [];
    }
}