<?php

namespace App\Services;

use GuzzleHttp\Client;

class ClaudeService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.anthropic.com/v1/']);
        $this->apiKey = get_option("ai_claude_api_key", "");
    }

    public function getModels()
    {
        return [
            "claude-3-opus-20240229"       => __("Claude 3 Opus (strongest, best for complex tasks)"),
            "claude-3-sonnet-20240229"     => __("Claude 3 Sonnet (fast, balanced cost/performance)"),
            "claude-3-haiku-20240307"      => __("Claude 3 Haiku (cheapest, ultra-fast for basic tasks)"),
            "claude-3.7-sonnet-20250224"   => __("Claude 3.7 Sonnet (supports hybrid reasoning mode)"),
            "claude-4-opus-20250522"       => __("Claude 4 Opus (top-tier, best for coding & reasoning tasks)"),
            "claude-4-sonnet-20250522"     => __("Claude 4 Sonnet (balanced, fast, free-tier capable)"),
        ];
    }

    public function generateText($content, $maxTokens = 1024)
    {
        $model = get_option("ai_claude_model", "claude-3-sonnet-20240229");

        try {
            $response = $this->client->request('POST', 'messages', [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $content
                        ]
                    ]
                ],
                'timeout' => 60,
            ]);

            $body = json_decode($response->getBody(), true);

            return [
                "data" => [$body['content'][0]['text'] ?? ''],
                "promptTokens" => $body['usage']['input_tokens'] ?? 0,
                "completionTokens" => $body['usage']['output_tokens'] ?? 0,
                "totalTokens" => $body['usage']['input_tokens'] + $body['usage']['output_tokens'] ?? 0,
                "model" => $model
            ];

        } catch (\Throwable $e) {
            return [
                "data" => [],
                "promptTokens" => 0,
                "completionTokens" => 0,
                "totalTokens" => 0,
                "model" => $model,
                "error" => $e->getMessage()
            ];
        }
    }
}
