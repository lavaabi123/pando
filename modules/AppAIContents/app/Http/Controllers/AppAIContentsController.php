<?php

namespace Modules\AppAIContents\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use AI;
use Modules\AppAIContents\Services\AIContentStrategyService;
use Modules\AppAIContents\Models\ContentPlan;
use Modules\AppAIContents\Models\ContentCalendarItem;
use Modules\AppAIContents\Models\TopicResearch;
use Modules\AppAIContents\Models\CompetitorAnalysis;
use Modules\AppAIContents\Models\HashtagResearch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AppAIContentsController extends Controller
{
    protected string $templateTable = "ai_templates";
    protected string $categoryTable = "ai_categories";

    public function index()
    {
        return view('appaicontents::index');
    }

    public function categories(Request $request)
    {
        $categories = DB::table($this->categoryTable)
            ->where("status", 1)
            ->get();

        return ms([
            "status" => 1,
            "data"   => view(module("key") . '::categories', [
                "categories" => $categories,
            ])->render()
        ]);
    }

    public function templates(Request $request)
    {
        $category = DB::table($this->categoryTable)
            ->where([
                "id_secure" => $request->id,
                "status"    => 1,
            ])->first();

        if (empty($category)) {
            return ms([
                "status"  => 0,
                "message" => __("Category does not exist")
            ]);
        }

        $templates = DB::table($this->templateTable)
            ->where([
                "cate_id" => $category->id,
                "status"  => 1
            ])->get();

        return ms([
            "status" => 1,
            "data"   => view(module("key") . '::templates', [
                "category"  => $category,
                "templates" => $templates,
            ])->render()
        ]);
    }

    public function process(Request $request, string $page = "default")
    {
        $prompt     = trim($request->prompt ?? '');
        $aiOptions  = $request->ai_options ?? [];

        if ($prompt === '') {
            return ms([
                "status"  => "error",
                "message" => __("Please enter your prompt")
            ]);
        }

        $language     = $aiOptions['language']      ?? 'en-US';
        $maxLength    = (int) ($aiOptions['max_length'] ?? 100);
        $toneOfVoice  = $aiOptions['tone_of_voice'] ?? 'Friendly';
        $creativity   = $aiOptions['creativity']    ?? 0.5;
        $hashtags     = (int) ($aiOptions['hashtags'] ?? 0);
        $maxResult    = (int) ($aiOptions['number_result'] ?? 3);

        $maxResult = max(1, min($maxResult, 10)); // đảm bảo 1–10

        // Build prompt content
        $content = $this->buildPrompt($prompt, $language, $maxLength, $toneOfVoice, $creativity, $hashtags);

        try {
            $result = AI::process($content, 'text', [
                'maxResult' => $maxResult
            ]);
        } catch (\Throwable $e) {
            return ms([
                "status"  => 0,
                "message" => $e->getMessage(),
            ]);
        }

        $view = $page === 'popup' ? 'popup_result' : 'result';

        return ms([
            "status" => 1,
            "data"   => view(module("key") . '::' . $view, [
                "result" => $result['data'] ?? [],
            ])->render()
        ]);
    }

    public function popupAIContent()
    {
        return ms([
            "status" => 1,
            "data"   => view(module("key") . '::popup')->render()
        ]);
    }

    public function createContent(Request $request)
    {
        $content = trim($request->content ?? '');

        if ($content === '') {
            return ms([
                "status"  => "error",
                "message" => __("Please enter your prompt")
            ]);
        }

        try {
            $result = AI::process($content, 'text', ['maxResult' => 1]);

            return ms([
                "status" => 1,
                "data"   => $result['data'][0] ?? ''
            ]);
        } catch (\Throwable $e) {
            return ms([
                "status"  => 0,
                "message" => $e->getMessage(),
            ]);
        }
    }

    /** ---------------- Helper ---------------- */
    protected function buildPrompt(
        string $prompt,
        string $language,
        int $maxLength,
        string $toneOfVoice,
        float $creativity,
        int $hashtags = 0
    ): string {
        if ($hashtags > 0) {
            return "Create a paragraph about the content '{$prompt}' including {$hashtags} hashtags at the end of each paragraph with a maximum of {$maxLength} characters. Creativity is {$creativity} between 0 and 1. Use the {$language} language. Tone of voice must be {$toneOfVoice}.";
        }

        return "Create a paragraph about the content '{$prompt}' with a maximum of {$maxLength} words. Creativity is {$creativity} between 0 and 1. Use the {$language} language. Tone of voice must be {$toneOfVoice}.";
    }
	
	
	// ADD THESE METHODS TO AppAIContentsController.php (before the closing } )

	/**
	 * Show content calendar generation page
	 */
	public function showContentStrategy()
	{
		return view('appaicontents::content_strategy');
	}

	/**
	 * Generate AI-powered content calendar
	 */
/**
 * Generate AI-powered content calendar
 */
	public function generateContentCalendar(Request $request)
	{
		// Check if user is authenticated
		if (!auth()->check()) {
			return ms([
				'status' => 0,
				'message' => 'You must be logged in to generate content calendar'
			]);
		}

		$request->validate([
			'name' => 'required|string|max:255',
			'business_goals' => 'required|string',
			'target_audience' => 'required|string',
			'platforms' => 'required|array',
			'start_date' => 'required|date',
			'end_date' => 'required|date|after:start_date',
			'posting_frequency' => 'required|string',
		]);

		try {
			// Get authenticated user ID
			$userId = auth()->id();

			// Create content plan
			$contentPlan = ContentPlan::create([
				'user_id' => $userId,
				'name' => $request->name,
				'description' => $request->description ?? "AI Generated Content Plan",
				'business_goals' => [$request->business_goals],
				'target_audience' => [$request->target_audience],
				'platforms' => $request->platforms,
				'start_date' => $request->start_date,
				'end_date' => $request->end_date,
				'status' => 'draft'
			]);

			// Generate calendar using AI
			$aiService = new AIContentStrategyService($userId);
			$result = $aiService->generateContentCalendar($request->all());

			if (!$result['success']) {
				return ms([
					'status' => 0,
					'message' => 'Failed to generate content calendar',
					'error' => $result['error']
				]);
			}

			// Save calendar items
			$calendarItems = [];
			if (isset($result['calendar']['calendar'])) {
				foreach ($result['calendar']['calendar'] as $item) {
					$calendarItem = ContentCalendarItem::create([
						'content_plan_id' => $contentPlan->id,
						'user_id' => $userId,
						'title' => $item['title'] ?? 'Untitled',
						'content' => $item['content'] ?? '',
						'platform_versions' => $item['platform_versions'] ?? null,
						'content_type' => $item['content_type'] ?? 'post',
						'suggested_hashtags' => $item['hashtags'] ?? [],
						'suggested_post_time' => Carbon::parse($item['date'] . ' ' . ($item['time'] ?? '09:00')),
						'topic_category' => $item['content_type'] ?? null,
						'status' => 'suggested'
					]);
					
					// Convert to array and add to collection
					$calendarItems[] = $calendarItem->toArray();
				}
			}

			// IMPORTANT: Convert everything to arrays using toArray()
			return response()->json([
				'status' => 1,
				'message' => 'Content calendar generated successfully!',
				'data' => [
					'content_plan' => $contentPlan->toArray(),  // ← Convert to array
					'calendar_items' => $calendarItems,         // ← Already arrays
					'strategy_notes' => $result['calendar']['strategy_notes'] ?? null
				]
			]);

		} catch (\Exception $e) {
			\Log::error('Content Calendar Error: ' . $e->getMessage());
			\Log::error('Stack trace: ' . $e->getTraceAsString());
			
			return ms([
				'status' => 0,
				'message' => 'Error generating content calendar',
				'error' => $e->getMessage()
			]);
		}
	}

	/**
	 * Research hashtags for content
	 */
	public function researchHashtags(Request $request)
	{
		if (!auth()->check()) {
			return ms(['status' => 0, 'message' => 'Authentication required']);
		}

		$request->validate([
			'content' => 'required|string',
			'platform' => 'required|string',
			'industry' => 'required|string',
			'count' => 'nullable|integer|min:5|max:30'
		]);

		try {
			$userId = auth()->id();
			$aiService = new AIContentStrategyService($userId);
			
			$result = $aiService->researchHashtags(
				$request->content,
				$request->platform,
				$request->industry,
				$request->count ?? 15
			);

			if (!$result['success']) {
				return ms([
					'status' => 0,
					'message' => 'Failed to research hashtags',
					'error' => $result['error']
				]);
			}

			// Return as JSON response (already arrays from AI service)
			return response()->json([
				'status' => 1,
				'message' => 'Hashtags researched successfully!',
				'data' => $result['hashtag_research']
			]);

		} catch (\Exception $e) {
			\Log::error('Hashtag Research Error: ' . $e->getMessage());
			
			return ms([
				'status' => 0,
				'message' => 'Error researching hashtags',
				'error' => $e->getMessage()
			]);
		}
	}

	/**
	 * Research topics and trends
	 */
	public function researchTopics(Request $request)
	{
		if (!auth()->check()) {
			return ms(['status' => 0, 'message' => 'Authentication required']);
		}

		$request->validate([
			'topics' => 'required|string',
			'industry' => 'required|string',
			'target_audience' => 'nullable|string'
		]);

		try {
			$userId = auth()->id();
			$aiService = new AIContentStrategyService($userId);
			
			// Split topics by comma
			$topics = array_map('trim', explode(',', $request->topics));
			$targetAudience = $request->target_audience ? [$request->target_audience] : [];
			
			$result = $aiService->researchTopics($topics, $request->industry, $targetAudience);

			if (!$result['success']) {
				return ms([
					'status' => 0,
					'message' => 'Failed to research topics',
					'error' => $result['error']
				]);
			}

			return response()->json([
				'status' => 1,
				'message' => 'Topics researched successfully!',
				'data' => $result
			]);

		} catch (\Exception $e) {
			\Log::error('Topic Research Error: ' . $e->getMessage());
			
			return ms([
				'status' => 0,
				'message' => 'Error researching topics',
				'error' => $e->getMessage()
			]);
		}
	}

	/**
	 * Analyze competitor
	 */
	public function analyzeCompetitor(Request $request)
	{
		if (!auth()->check()) {
			return ms(['status' => 0, 'message' => 'Authentication required']);
		}

		$request->validate([
			'name' => 'required|string',
			'industry' => 'required|string',
			'url' => 'nullable|url',
			'platforms' => 'nullable|array',
			'sample_content' => 'nullable|string'
		]);

		try {
			$userId = auth()->id();
			$aiService = new AIContentStrategyService($userId);
			
			// Prepare competitor data
			$competitorData = [
				'name' => $request->name,
				'url' => $request->url,
				'industry' => $request->industry,
				'platforms' => $request->platforms ?? [],
				'sample_content' => $request->sample_content ? 
					array_map('trim', explode("\n", $request->sample_content)) : []
			];
			
			$result = $aiService->analyzeCompetitor($competitorData);

			if (!$result['success']) {
				return ms([
					'status' => 0,
					'message' => 'Failed to analyze competitor',
					'error' => $result['error']
				]);
			}

			return response()->json([
				'status' => 1,
				'message' => 'Competitor analyzed successfully!',
				'data' => $result
			]);

		} catch (\Exception $e) {
			\Log::error('Competitor Analysis Error: ' . $e->getMessage());
			
			return ms([
				'status' => 0,
				'message' => 'Error analyzing competitor',
				'error' => $e->getMessage()
			]);
		}
	}
	/**
	 * Adapt content for multiple platforms
	 */
	public function adaptContent(Request $request)
	{
		$request->validate([
			'content' => 'required|string',
			'platforms' => 'required|array',
		]);

		try {
			$aiService = new AIContentStrategyService(auth()->id());
			$result = $aiService->adaptContentForPlatforms(
				$request->content,
				$request->platforms,
				[
					'brand_voice' => $request->brand_voice,
					'target_audience' => $request->target_audience,
					'goal' => $request->goal
				]
			);

			return ms([
				'status' => $result['success'] ? 1 : 0,
				'message' => $result['success'] ? 'Content adapted successfully' : 'Failed to adapt content',
				'data' => $result['platform_versions'] ?? null
			]);

		} catch (\Exception $e) {
			\Log::error('Content Adaptation Error: ' . $e->getMessage());
			return ms([
				'status' => 0,
				'message' => 'Error adapting content',
				'error' => $e->getMessage()
			]);
		}
	}

	/**
	 * Get content plans list
	 */
	public function getContentPlans()
	{
		try {
			$plans = ContentPlan::where('user_id', Auth::id())
				->with(['calendarItems'])
				->orderBy('created_at', 'desc')
				->paginate(15);

			return ms([
				'status' => 1,
				'data' => $plans
			]);
		} catch (\Exception $e) {
			return ms([
				'status' => 0,
				'message' => 'Error fetching plans',
				'error' => $e->getMessage()
			]);
		}
	}
}
