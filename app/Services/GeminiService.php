<?php

namespace App\Services;

use GuzzleHttp\Client;

class GeminiService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.gemini.com/v1/']);
        $this->apiKey = get_option("ai_gemeni_api_key", "");
    }

    public function getModels()
    {
        try {
            $response = $this->client->request('GET', 'models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
            ]);

            $models = json_decode($response->getBody(), true);

            $arr = [];
            if (isset($models['data'])) {
                foreach ($models['data'] as $model) {
                    $arr[$model['id']] = $model['description'] ?? $model['id'];
                }
                return $arr;
            }
        } catch (\Throwable $e) {}

        return false;
    }

    public function generateText($content, $maxResult = 5)
    {
        $model = get_option("ai_gemini_model", "gemini-2.5-flash");

        $payload = [
            'model'    => $model,
            'messages' => [
                [
                    "role" => "user",
                    "content" => $content,
                ]
            ],
            'n' => $maxResult,
        ];

        try {
            $response = $this->client->request('POST', 'chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'body' => json_encode($payload),
                'timeout' => 60,
            ]);

            $body = json_decode($response->getBody(), true);

            $result = [];
            if (!empty($body['choices'])) {
                foreach ($body['choices'] as $choice) {
                    $result[] = $choice['message']['content'] ?? '';
                }
            }

            return [
                "data" => $result,
                "promptTokens" => $body['usage']['prompt_tokens'] ?? 0,
                "completionTokens" => $body['usage']['completion_tokens'] ?? 0,
                "totalTokens" => $body['usage']['total_tokens'] ?? 0,
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
