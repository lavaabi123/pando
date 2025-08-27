<?php

namespace App\Services;

use GuzzleHttp\Client;
use OpenAI;

class OpenAIService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = get_option("ai_openai_api_key");
        $this->client = OpenAI::client($this->apiKey);
    }

    public function getModels()
    {
        $response = $this->client->models()->list();
        $arr = [];
        if (!empty($response->data)) {
            foreach ($response->data as $model) {
                $arr[$model->id] = $model->id;
            }
        }
        return $arr;
    }

    public function generateText($prompt, $maxLength, $maxResult = 5)
    {
        $model = get_option("ai_openai_model", "gpt-4-turbo");

        $messages = is_array($prompt)
            ? $prompt
            : [[
                "role" => "user",
                "content" => $prompt,
            ]];

        try {
            $response = $this->client->chat()->create([
                'model' => $model,
                'messages' => $messages,
                'max_completion_tokens' => (int)$maxLength,
                'n' => $maxResult
            ]);
        } catch (\Throwable $e) {
            return [
                'data' => [],
                'promptTokens' => 0,
                'completionTokens' => 0,
                'totalTokens' => 0,
                'error' => $e->getMessage(),
            ];
        }

        $result = [];
        if (!empty($response->choices)) {
            foreach ($response->choices as $choice) {
                $result[] = is_object($choice->message) ? $choice->message->content : $choice['message']['content'];
            }
        }

        return [
            "data" => $result,
            "promptTokens" => $response->usage->promptTokens ?? 0,
            "completionTokens" => $response->usage->completionTokens ?? 0,
            "totalTokens" => $response->usage->totalTokens ?? 0,
            "model" => $model
        ];
    }
}