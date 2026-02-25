<?php

namespace Modules\AppPublishing\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\AppChannels\Models\Accounts;
use Modules\AppPublishing\Models\Posts;
use Modules\AppPublishing\Models\PostStat;
use UploadFile;

class PublishingService
{
    protected array $uploadedFiles = [];

    /**
     * Validate a list of posts.
     */
    public function validate($posts)
    {
        $htmlErrors = '';
        $countErrors = 0;
        $socialPosts = [];
        $socialCanPosts = [];
        $canPost = false;
        $errors = [];

        foreach ($posts as $post) {
            if (empty($post->module) || empty($post->social_network)) continue;

            try {
                $module = $post->module;

                if (!class_exists($module)) {
                    $modulePath = "\\Modules\\{$module}\\Facades\\Post";
                    if (class_exists($modulePath)) class_alias($modulePath, $module);
                    else continue;
                }

                if (method_exists($module, 'validator')) {
                    $result = $module::validator($post);
                    if (!empty($result)) {
                        $errors[$post->social_network] = $result;
                        $socialPosts[] = ucfirst($post->social_network);
                    }
                }
            } catch (Exception $e) {
                Log::error("Validator error: " . $e->getMessage());
            }

            if (!isset($errors[$post->social_network])) {
                $errors[$post->social_network] = [];
                $socialPosts[] = ucfirst($post->social_network);
            }
        }

        foreach ($errors as $social => $subErrors) {
            if (empty($subErrors)) {
                $canPost = true;
                $socialCanPosts[] = $social;
            } else {
                foreach ($subErrors as $error) {
                    $htmlErrors .= "<li>{$error}</li>";
                }
                $countErrors++;
            }
        }

        if ($countErrors > 0) {
            $htmlErrors = "<p>" . __(
                ':count profiles will be excluded from your publication in next step due to errors',
                ['count' => $countErrors]
            ) . "</p><ul class='text-danger'>{$htmlErrors}</ul>";
        }

        $status = !$countErrors ? 1 : ($canPost ? 2 : 0);
        $message = "";

        if ($status === 0 && $countErrors === 1) {
            $lastError = end($errors);
            $message = __(is_array($lastError) ? $lastError[0] : $lastError);
        } elseif ($status === 0) {
            $message = __(
                'Missing content on the following social networks: :networks',
                ['networks' => implode(", ", array_unique($socialPosts))]
            );
        }

        return [
            "status"   => $status,
            "errors"   => $htmlErrors,
            "message"  => $message,
            "can_post" => json_encode($socialCanPosts),
        ];
    }

    /**
     * Publish posts on social networks.
     */
   public function post($posts, $socialCanPost = false)
{
    $postBy = request()->post_by;
    $teamId = request()->team_id;
    $postType = (!empty(request()->post_type) && request()->post_type == 'duplicate') ? request()->post_type : '';
    $postId = 0;
    $countError = 0;
    $countSuccess = 0;
    $countSchedule = 0;
    $message = "";
    $errMessages = [];

    // Only validate if not approval (post_by != 5)
    if ($postBy != 5) {
        if (empty($posts)) {
            return [
                "status" => 0,
                "message" => __('Accounts selected is inactive. Let re-login and try again')
            ];
        }
    }

    foreach ($posts as $post) {
        try {
            $fromRun = (!empty($post->from_run) && $post->from_run == 'cron') ? $post->from_run : '';
            unset($post->from_run);

            // Convert post to array
            if (is_null($post)) {
                $tmpPost = [];
            } elseif ($post instanceof \Illuminate\Database\Eloquent\Model || $post instanceof \Illuminate\Support\Collection) {
                $tmpPost = $post->toArray();
            } elseif ($post instanceof \stdClass) {
                $tmpPost = (array) $post;
            } elseif (is_array($post)) {
                $tmpPost = $post;
            } else {
                $tmpPost = json_decode(json_encode($post), true);
            }

            if (isset($post->team_id)) {
                $teamId = $post->team_id;
            }

            $socialNetwork = $post->social_network;
            $canPostThis = (is_array($socialCanPost) && in_array($socialNetwork, $socialCanPost)) || !$socialCanPost;

            if ($canPostThis) {
                // Check if module exists
                if (empty($post->module) || empty($post->social_network)) {
                    continue;
                }

                $module = $post->module;
                if (!class_exists($module)) {
                    $modulePath = "\\Modules\\{$module}\\Facades\\Post";
                    if (class_exists($modulePath)) {
                        class_alias($modulePath, $module);
                    } else {
                        continue;
                    }
                }

                if (method_exists($module, 'post')) {
                    // Check if we should post immediately or schedule
                    if (!request()->has('id_secure') || request()->input('draft')) {
                        if (isset($post->id) && !request()->input('draft')) {
                            $postId = $post->id;
                        }

                        // Get account
                        $account = Accounts::find($post->account_id);
                        
                        if (empty($account)) {
                            $countError++;
                            $message = __("This account does not exist");

                            // Update post status if editing
                            if (request()->has('id_secure')) {
                                $post->status = 5;
                                $post->result = json_encode(["message" => $message], JSON_UNESCAPED_UNICODE);
                                
                                $item = Posts::where('id_secure', $post->id_secure)->first();
                                if ($item) {
                                    Posts::where('grouping_data', $item->grouping_data)
                                        ->update([
                                            'status' => 5,
                                            'result' => json_encode(["message" => $message], JSON_UNESCAPED_UNICODE)
                                        ]);
                                }
                            }
                        } else {
                            // Post immediately or from cron
                            if ($postBy == 1 || isset($post->id)) {
                                // Check quota
                                $quota = $this->checkQuota($teamId);
                                if (!$quota['can_post']) {
                                    $countError++;
                                    $message = __('Post failed: Quota exceeded');
                                    $errMessages[] = ucfirst($socialNetwork) . '--' . $quota['message'];
                                    
                                    $post->status = 5;
                                    $post->result = json_encode([
                                        'message' => ucfirst($socialNetwork) . '--' . $quota['message'],
                                    ], JSON_UNESCAPED_UNICODE);
                                    
                                    if ($fromRun == 'cron') {
                                        Posts::where('id', $post->id)
                                            ->update([
                                                'status' => 5,
                                                'result' => $post->result
                                            ]);
                                    }
                                    
                                    $this->savePostStat($post, 5, $quota['message'], null);
                                    continue;
                                }

                                $post->account = $account;
                                $this->handleMediaPreprocessing($post);
                                
                                $response = $module::post($post) ?: [
                                    "status" => 0,
                                    "message" => __("Unknown error")
                                ];

                                if ($response["status"] == 1) {
                                    $countSuccess++;
                                    $message = $response["message"];
                                    $post->status = 4; // Published
                                    $post->result = json_encode([
                                        "id" => $response["id"] ?? null,
                                        "url" => $response["url"] ?? null,
                                        "message" => $response["message"]
                                    ], JSON_UNESCAPED_UNICODE);

                                    // Update team stats
                                    //$this->updateTeamStats($teamId, $socialNetwork, 'success', $response["type"] ?? null);
                                } else {
                                    $countError++;
                                    $message = $response["message"];
                                    $errMessages[] = ucfirst($socialNetwork) . '--' . $response["message"];
                                    
                                    $post->status = 5; // Failed
                                    $post->result = json_encode([
                                        "message" => ucfirst($socialNetwork) . '--' . $response["message"]
                                    ], JSON_UNESCAPED_UNICODE);

                                    // Update team stats
                                    //$this->updateTeamStats($teamId, $socialNetwork, 'error');
                                }

                                // Update status if from cron
                                if ($fromRun == 'cron') {
                                    Posts::where('id', $post->id)
                                        ->update([
                                            'status' => $post->status,
                                            'result' => $post->result
                                        ]);
                                }

                                // Save post stats
                                $postResponse = json_decode($post->result, true) ?? [];
                                $this->savePostStat($post, $post->status, $postResponse['message'] ?? null, $postResponse['id'] ?? null);

                                // Handle repost
                                if (($tmpPost['repost_frequency'] ?? 0) != 0) {
                                    $nextTime = $tmpPost['repost_frequency'] * 86400;
                                    unset($tmpPost['account'], $tmpPost['id']);

                                    if ($tmpPost['time_post'] < $tmpPost['repost_until']) {
                                        $post->repost_frequency = 0;
                                        $post->repost_until = null;
                                        
                                        if ($fromRun != 'cron') {
                                            $tmpPost['id_secure'] = rand_string();
                                            $tmpPost['result'] = null;
                                            $tmpPost['changed'] = time();
                                            $tmpPost['created'] = time();
                                            $tmpPost['time_post'] += $nextTime;
                                            
                                            if ($tmpPost['time_post'] <= time()) {
                                                $tmpPost['time_post'] = time() + $nextTime;
                                            }
                                            
                                            Posts::create($tmpPost);
                                        }
                                    }
                                }
                            } else {
                                $countSchedule++;
                            }

                            unset($post->account);

                            $postArr = (is_object($post) && method_exists($post, 'toArray'))
                                ? $post->toArray()
                                : (array) $post;

                            // Save or update post
                            if ($postId && !request()->input('draft') && $fromRun != 'cron') {
                                $item = Posts::where('id_secure', $postArr['id_secure'])->first();
                                
                                if (!empty($item) && empty($postType)) {
                                    Posts::where('grouping_data', $item->grouping_data)->delete();
                                }
                                
                                if ($fromRun != 'cron') {
                                    $postArr['id_secure'] = rand_string();
                                    Posts::create($postArr);
                                }
                            } else {
                                if ($fromRun != 'cron') {
                                    if (request()->input('draft') && $postArr['status'] == 0) {
                                        $item = Posts::where('id_secure', $postArr['id_secure'])->first();
                                        
                                        if (!empty($item) && empty($postType)) {
                                            Posts::where('grouping_data', $item->grouping_data)->delete();
                                        }
                                        
                                        $postArr['id_secure'] = rand_string();
                                        Posts::create($postArr);
                                    } else {
                                        $item = Posts::where('id_secure', $postArr['id_secure'])->first();
                                        
                                        if (!empty($item) && empty($postType)) {
                                            Posts::where('grouping_data', $item->grouping_data)->delete();
                                        }
                                        
                                        $postArr['id_secure'] = rand_string();
                                        Posts::create($postArr);
                                    }
                                }
                            }
                        }
                    } else {
                        // Updating existing post
                        $item = Posts::where('id_secure', $post->id_secure)->first();
                        
                        if (!empty($item)) {
                            if ($post->status == 0) {
                                if ($fromRun != 'cron') {
                                    $postArr = (is_object($post) && method_exists($post, 'toArray'))
                                        ? $post->toArray()
                                        : (array) $post;
                                    
                                    $postArr['id_secure'] = rand_string();
                                    Posts::create($postArr);
                                }
                            } else {
                                if ($fromRun != 'cron') {
                                    if (empty($postType)) {
                                        Posts::where('grouping_data', $item->grouping_data)->delete();
                                    }
                                    
                                    $postArr = (is_object($post) && method_exists($post, 'toArray'))
                                        ? $post->toArray()
                                        : (array) $post;
                                    
                                    $postArr['id_secure'] = rand_string();
                                    Posts::create($postArr);
                                }
                            }
                        } elseif (empty($item)) {
                            if ($fromRun != 'cron') {
                                $postArr = (is_object($post) && method_exists($post, 'toArray'))
                                    ? $post->toArray()
                                    : (array) $post;
                                
                                $postArr['id_secure'] = rand_string();
                                Posts::create($postArr);
                            }
                        } else {
                            return [
                                "status" => 0,
                                "message" => __("Can't update this post")
                            ];
                        }
                    }
                } elseif ($postBy == 5) {
                    // Save to approval
                    if ($post->id_secure) {
                        if ($fromRun != 'cron') {
                            $item = Posts::where('id_secure', $post->id_secure)->first();
                            
                            if (!empty($item) && empty($postType)) {
                                Posts::where('grouping_data', $item->grouping_data)->delete();
                            }
                            
                            $postArr = (is_object($post) && method_exists($post, 'toArray'))
                                ? $post->toArray()
                                : (array) $post;
                            
                            $postArr['id_secure'] = rand_string();
                            Posts::create($postArr);
                        }
                    } else {
                        if ($fromRun != 'cron') {
                            $postArr = (is_object($post) && method_exists($post, 'toArray'))
                                ? $post->toArray()
                                : (array) $post;
                            
                            $postArr['id_secure'] = rand_string();
                            Posts::create($postArr);
                        }
                    }
                } else {
                    $countError++;
                    $message = __("Can't post to this social network");

                    if ($postId) {
                        $item = Posts::where('id', $postId)->first();
                        if ($item) {
                            Posts::where('grouping_data', $item->grouping_data)
                                ->update([
                                    'status' => 5,
                                    'result' => json_encode(["message" => $message], JSON_UNESCAPED_UNICODE)
                                ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $countError++;
            $errMessages[] = ucfirst($socialNetwork ?? 'Unknown') . '--' . $e->getMessage();
            
            $this->savePostStat($post, 5, $e->getMessage(), null);

            $postArr = (is_object($post) && method_exists($post, 'toArray'))
                ? $post->toArray()
                : (array) $post;

            unset($postArr['account'], $postArr['id']);
            $postArr['status'] = 5;
            $postArr['result'] = json_encode(["message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            
            if ($postId) {
                Posts::where('id', $postId)->update($postArr);
            } else {
                Posts::create($postArr);
            }
        }
    }

    // Cleanup uploaded files
    $this->cleanupFiles();

    // Return appropriate response based on post_by value
    if ($postBy == 1 || isset($post->id)) {
        if ($countError == 0) {
            return [
                "status" => 1,
                "message" => sprintf(__("Your video is being uploaded to TikTok. It may take a few minutes for your content to process and be visible on your profile."), $countSuccess)
            ];
        } elseif ($countError == 1 && $countSuccess == 0) {
            return [
                "status" => 0,
                "message" => $message
            ];
        } else {
            return [
                "status" => 0,
                "message" => sprintf(
                    __("Content is being published on %d profiles and %d profiles unpublished(%s)"), 
                    $countSuccess, 
                    $countError, 
                    implode(', ', $errMessages)
                )
            ];
        }
    } elseif ($postBy == 4) {
        return [
            "status" => 1,
            "message" => __("Post saved to draft folder")
        ];
    } elseif ($postBy == 5) {
        return [
            "status" => 1,
            "message" => __("Post saved to approval folder")
        ];
    } else {
        return [
            "status" => 1,
            "message" => __("Content successfully scheduled")
        ];
    }
}

// Helper method to update team stats
protected function updateTeamStats($teamId, $socialNetwork, $type, $postType = null)
{
    if ($type == 'success') {
        // Increment success count
        $currentSuccess = TeamData::where('team_id', $teamId)
            ->where('key', $socialNetwork . '_post_success_count')
            ->value('value') ?? 0;
        
        TeamData::updateOrCreate(
            ['team_id' => $teamId, 'key' => $socialNetwork . '_post_success_count'],
            ['value' => $currentSuccess + 1]
        );
        
        // Increment total count
        $currentTotal = TeamData::where('team_id', $teamId)
            ->where('key', $socialNetwork . '_post_count')
            ->value('value') ?? 0;
        
        TeamData::updateOrCreate(
            ['team_id' => $teamId, 'key' => $socialNetwork . '_post_count'],
            ['value' => $currentTotal + 1]
        );
        
        // Increment post type count if provided
        if ($postType) {
            $currentTypeCount = TeamData::where('team_id', $teamId)
                ->where('key', $socialNetwork . '_post_' . $postType . '_count')
                ->value('value') ?? 0;
            
            TeamData::updateOrCreate(
                ['team_id' => $teamId, 'key' => $socialNetwork . '_post_' . $postType . '_count'],
                ['value' => $currentTypeCount + 1]
            );
        }
    } elseif ($type == 'error') {
        // Increment error count
        $currentError = TeamData::where('team_id', $teamId)
            ->where('key', $socialNetwork . '_post_error_count')
            ->value('value') ?? 0;
        
        TeamData::updateOrCreate(
            ['team_id' => $teamId, 'key' => $socialNetwork . '_post_error_count'],
            ['value' => $currentError + 1]
        );
        
        // Increment total count
        $currentTotal = TeamData::where('team_id', $teamId)
            ->where('key', $socialNetwork . '_post_count')
            ->value('value') ?? 0;
        
        TeamData::updateOrCreate(
            ['team_id' => $teamId, 'key' => $socialNetwork . '_post_count'],
            ['value' => $currentTotal + 1]
        );
    }
}
    /**
     * Xử lý media (convert b64_json → file url)
     */
    protected function handleMediaPreprocessing(&$post)
    {
        if ($post->type === 'media' && !empty($post->data)) {
            $data = is_string($post->data) ? json_decode($post->data, true) : (array)$post->data;
            $normalized = [];

            foreach ($data['medias'] ?? [] as $media) {
                if (isset($media['b64_json'])) {
                    $ext = str_contains($media['mimeType'] ?? '', 'png') ? 'png' : 'jpg';
                    $fileName = uniqid('aiimg_') . '.' . $ext;
                    $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

                    file_put_contents($tempPath, base64_decode($media['b64_json']));

                    $fileUrl = UploadFile::storeSingleFile(new \Illuminate\Http\File($tempPath), 'uploads');

                    $this->uploadedFiles[] = $fileUrl;
                    @unlink($tempPath);

                    $normalized[] = $fileUrl;
                } elseif (isset($media['url'])) {
                    $normalized[] = $media;
                }
            }

            if (!empty($normalized)) {
                $data['medias'] = \Watermark::createWatermarkedList($normalized, $post->account_id);
            }

            $post->data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return $post;
    }

    protected function cleanupFiles()
    {
        foreach ($this->uploadedFiles as $fileUrl) {
            try {
                UploadFile::deleteFileFromServer($fileUrl);
            } catch (\Throwable $e) {
                Log::warning("Failed to delete uploaded file {$fileUrl}: " . $e->getMessage());
            }
        }
        $this->uploadedFiles = [];
    }

    private function savePostStat($post, $status, $message = null, $post_social_id = null)
    {
        $data = ($post instanceof Posts)
            ? $post->toArray()
            : (is_object($post) ? (array)$post : $post);

        return PostStat::create([
            'id_secure'      => $data['id_secure']      ?? null,
            'post_id'        => $data['id']             ?? null,
            'team_id'        => $data['team_id']        ?? null,
            'user_id'        => $data['user_id']        ?? null,
            'account_id'     => $data['account_id']     ?? null,
            'social_network' => $data['social_network'] ?? null,
            'campaign'       => $data['campaign']       ?? null,
            'labels'         => isset($data['labels']) && is_array($data['labels'])
                ? json_encode($data['labels'], JSON_UNESCAPED_UNICODE)
                : ($data['labels'] ?? null),
            'category'       => $data['category']       ?? null,
            'module'         => $data['module']         ?? null,
            'type'           => $data['type']           ?? null,
            'method'         => $data['method']         ?? null,
            'query_id'       => $data['query_id']       ?? null,
            'status'         => $status,
            'post_social_id' => $post_social_id,
            'created'        => $data['created'] ?? time(),
            'message'        => $message,
			'brand_id' => $data['brand_id'] ?? session('brand_id'),
        ]);
    }

    public function checkQuota($teamId = null)
    {
        $teamId = $teamId ?? request()->team_id;
        $maxPost = \UserInfo::getTeamPermission('apppublishing.max_post', 0, $teamId);

        if ($maxPost == -1 || $maxPost === '-1') {
            return [
                'can_post' => true,
                'limit' => -1,
                'used' => 0,
                'left' => -1,
                'message' => __("Your team has unlimited posts for this plan."),
            ];
        }

        $quotaResetAt = \UserInfo::getDataTeam('quota_reset_at', null, $teamId);
        $nextQuotaResetAt = \UserInfo::getDataTeam('next_quota_reset_at', null, $teamId);
        $startTimestamp = $quotaResetAt ? intval($quotaResetAt) : now()->startOfMonth()->timestamp;
        $endTimestamp = $nextQuotaResetAt;

        $used = PostStat::where('team_id', $teamId)->where('brand_id', session('brand_id'))
            ->where('status', 4)
            ->whereBetween('created', [$startTimestamp, $endTimestamp])
            ->count();

        $left = max(0, intval($maxPost) - $used);

        return [
            'can_post' => $used < $maxPost,
            'limit'    => intval($maxPost),
            'used'     => $used,
            'left'     => $left,
            'message'  => $used < $maxPost
                ? __("Your account has :count posts left in this month's quota.", ['count' => $left])
                : __("Your account has reached its monthly post quota. Please upgrade your plan or wait for the next month."),
        ];
    }

    public function moduleCanPost()
    {
        $modulesPath = base_path('modules');
        $modules = scandir($modulesPath);
        $postModules = [];

        foreach ($modules as $module) {
            if ($module === '.' || $module === '..') continue;
            if (file_exists("$modulesPath/$module/Facades/Post.php")) {
                $postModules[] = $module;
            }
        }

        return $postModules;
    }
}
