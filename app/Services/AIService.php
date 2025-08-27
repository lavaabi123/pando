<?php

namespace App\Services;

use App\Services\OpenAIService;
use App\Services\DeepSpeekService;
use App\Services\GeminiService;
use App\Services\ClaudeService;

class AIService
{
    protected $OpenAIService;
    protected $DeepSpeekService;
    protected $GeminiService;
    protected $ClaudeService;

    public function __construct(
        OpenAIService $OpenAIService,
        DeepSpeekService $DeepSpeekService,
        GeminiService $GeminiService,
        ClaudeService $ClaudeService
    ) {
        $this->OpenAIService = $OpenAIService;
        $this->DeepSpeekService = $DeepSpeekService;
        $this->GeminiService = $GeminiService;
        $this->ClaudeService = $ClaudeService;
    }

    public function process($content, $maxResult = null, $teamId = 0)
    {
        $maxLength = get_option("ai_max_output_lenght", 1000);
        $model = get_option("ai_platform", "openai");
        $quota = \Credit::checkQuota($teamId);

        if (!$quota['can_use']) {
            throw new \Exception($quota['message']);
        }      

        $response = match ($model) {
            'openai'   => $this->OpenAIService->generateText($content, $maxLength, $maxResult),
            'deepseek' => $this->DeepSpeekService->generateText($content, $maxLength, $maxResult),
            'gemini'   => $this->GeminiService->generateText($content, $maxResult),
            'claude'   => $this->ClaudeService->generateText($content, $maxLength, $maxResult),
            default    => throw new \Exception('Model not supported'),
        };

        $tokensUsed = $response['totalTokens'] ?? 0;
        $creditsUsed = \Credit::convertToCredits( $response['model'] ?? '' , $tokensUsed);
        \Credit::trackUsage($creditsUsed, 'ai_words', $model, $teamId);

        return $response;
    }

    public function getModels($model)
    {
        return match ($model) {
            'openai'   => $this->OpenAIService->getModels(),
            'deepseek' => $this->DeepSpeekService->getModels(),
            'gemini'   => $this->GeminiService->getModels(),
            'claude'   => $this->ClaudeService->getModels(),
            default    => throw new \Exception('Model not supported'),
        };
    }

    public function getModelDescription($platform, $model)
    {
        $models = $this->getAvailableModels($platform);
        return $models[$model] ?? __('Unknown model');
    }

    public function getPlatforms()
    {
        return [
            "openai"   => __("OpenAI"),
            "deepseek" => __("Deepseek"),
            "gemini"   => __("Gemini"),
            "claude"   => __("Claude"),
        ];
    }

    public function getAvailableModels($model)
    {
        switch ($model) {
            case 'openai':
                return [
                    "gpt-4.5-turbo" => __("GPT-4.5 Turbo - (Newest, fastest, most capable general-purpose model for all text tasks)"),
                    "gpt-4o" => __("GPT-4o - (Unified model for text, vision, audio; excellent general text and reasoning)"),
                    "gpt-4o-mini" => __("GPT-4o Mini - (Compact, efficient, suitable for scalable text generation)"),
                    "gpt-4-turbo" => __("GPT-4 Turbo - (Faster, lower cost, great for production text tasks)"),
                    "gpt-4" => __("GPT-4 - (Advanced, high-quality general text generation)"),
                    "gpt-3.5-turbo" => __("GPT-3.5 Turbo - (Cost-effective, fast, general text and chat)"),
                    "gpt-3.5-turbo-instruct" => __("GPT-3.5 Turbo Instruct - (Instruction-following, suitable for structured tasks)"),
                ];
            case 'deepseek':
                return [
                    "deepseek-v3" => __("DeepSeek V3 - (Newest generalist model for advanced and fluent text generation)"),
                    "deepseek-llm-67b-chat" => __("DeepSeek LLM 67B Chat - (Large-scale, advanced reasoning, optimized for general chat and text)"),
                    "deepseek-llm-7b-chat" => __("DeepSeek LLM 7B Chat - (Efficient, general-purpose conversational and text model)"),
                    "deepseek-llm-7b-base" => __("DeepSeek LLM 7B Base - (Base version for flexible general text tasks)"),
                    "deepseek-v3-base" => __("DeepSeek V3 Base - (Base version of DeepSeek V3, for robust text generation)"),
                    "deepseek-moe-16b-base" => __("DeepSeek MoE 16B Base - (Mixture-of-Experts, large general and specialized capabilities)"),
                    "deepseek-r1" => __("DeepSeek R1 - (First-gen reasoning model for general and logical text)"),
                    "deepseek-r1-distill-qwen-7b" => __("DeepSeek R1 Distill Qwen 7B - (Distilled from Qwen 2.5, efficient for text generation)"),
                    "deepseek-r1-distill-qwen-14b" => __("DeepSeek R1 Distill Qwen 14B - (Larger distilled model for diverse text tasks)"),
                    "deepseek-vl2" => __("DeepSeek VL2 - (Image-Text-to-Text model, supports multimodal generation)"),
                    "deepseek-vl2-tiny" => __("DeepSeek VL2 Tiny - (Lightweight version for basic image-text tasks)"),
                ];
            case 'gemini':
                return [
                    "gemini-2.5-flash" => __("Gemini 2.5 Flash - (Newest, fastest, accurate, optimized for general real-time text tasks)"),
                    "gemini-2.5-flash-lite" => __("Gemini 2.5 Flash Lite - (Ultra fast, low-cost, suitable for large volume and latency-sensitive text)"),
                    "gemini-2.5-pro" => __("Gemini 2.5 Pro - (Advanced reasoning, best-in-class for complex text and multimodal tasks)"),
                    "gemini-2.0-flash" => __("Gemini 2.0 Flash - (Previous gen, versatile, multimodal, fast text generation)"),
                    "gemini-2.0-flash-lite" => __("Gemini 2.0 Flash Lite - (Optimized for cost efficiency, low latency text tasks)"),
                    "gemini-2.0-pro" => __("Gemini 2.0 Pro - (Improved capabilities for native tool use and general text)"),
                    "gemini-1.5-pro" => __("Gemini 1.5 Pro - (Good for reasoning and complex text tasks)"),
                    "gemini-1.5-flash" => __("Gemini 1.5 Flash - (General fast text, prior generation)"),
                    "gemini-1.5-flash-8b" => __("Gemini 1.5 Flash 8B - (Optimized for high-volume, basic text tasks)"),
                    "gemini-2.0-flash-thinking" => __("Gemini 2.0 Flash Thinking - (Specialized for complex reasoning)"),
                ];
            case 'claude':
                return [
                    "claude-3-opus-20240229"       => __("Claude 3 Opus (strongest, best for complex tasks)"),
                    "claude-3-sonnet-20240229"     => __("Claude 3 Sonnet (fast, balanced cost/performance)"),
                    "claude-3-haiku-20240307"      => __("Claude 3 Haiku (cheapest, ultra-fast for basic tasks)"),
                    "claude-3.7-sonnet-20250224"   => __("Claude 3.7 Sonnet (supports hybrid reasoning mode)"),
                    "claude-4-opus-20250522"       => __("Claude 4 Opus (top-tier, best for coding & reasoning tasks)"),
                    "claude-4-sonnet-20250522"     => __("Claude 4 Sonnet (balanced, fast, free-tier capable)"),
                ];
            default:
                throw new \Exception('Model not supported');
        }
    }
}