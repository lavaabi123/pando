<?php

namespace App\Services;

use GuzzleHttp\Client;

class GeminiService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = get_option("ai_gemeni_api_key", "");
        $this->client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/',
        ]);
    }

    public function getModels()
    {
        try {
            $response = $this->client->request('GET', '', [
                'query' => ['key' => $this->apiKey],
                'timeout' => 30,
            ]);

            $models = json_decode($response->getBody(), true);

            $arr = [];
            if (!empty($models['models'])) {
                foreach ($models['models'] as $model) {
                    $id = $model['name'] ?? '';
                    $arr[$id] = $model['displayName'] ?? $id;
                }
            }

            return $arr;

        } catch (\Throwable $e) {
            return [
                "error" => $e->getMessage()
            ];
        }
    }

    public function generateText($content, $maxResult = 1)
    {
        $model = get_option("ai_gemini_model", "gemini-2.0-flash");

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $content]
                    ]
                ]
            ],
            "generationConfig" => [
                "candidateCount" => $maxResult
            ]
        ];

        try {
            $response = $this->client->request('POST', "models/{$model}:generateContent", [
                'query' => ['key' => $this->apiKey],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($payload),
                'timeout' => 60,
            ]);

            $body = json_decode($response->getBody(), true);

            $result = [];
            if (!empty($body['candidates'])) {
                foreach ($body['candidates'] as $candidate) {
                    $result[] = $candidate['content']['parts'][0]['text'] ?? '';
                }
            }

            return [
                "data" => $result,
                "model" => $model,
            ];

        } catch (\Throwable $e) {
            return [
                "data" => [],
                "model" => $model,
                "error" => $e->getMessage()
            ];
        }
    }

}
