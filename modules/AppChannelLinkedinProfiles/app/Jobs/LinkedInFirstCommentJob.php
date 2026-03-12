<?php

namespace Modules\AppChannelLinkedinProfiles\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LinkedInFirstCommentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // allow up to 2 mins for retries
    public $tries   = 1;

    public function __construct(
        private string $accessToken,
        private string $person_id,
        private string $type,       // $this->type from LinkedIn class
        private string $postUrn,
        private string $first_comment
    ) {}

    public function handle(): void
    {
        $activityUrn = null;
        $maxTries    = 8;
        $tryCount    = 0;

        while ($tryCount < $maxTries) {
            sleep(4);
            $tryCount++;

            $ch = curl_init("https://api.linkedin.com/v2/ugcPosts/" . rawurlencode($this->postUrn));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $this->accessToken,
                    'X-Restli-Protocol-Version: 2.0.0',
                ],
            ]);
            $ugcRaw  = curl_exec($ch);
            curl_close($ch);
            $ugcData = json_decode($ugcRaw, true);

            if (!empty($ugcData['activity'])) {
                $activityUrn = $ugcData['activity'];
                break;
            }
        }

        $commentUrn = $activityUrn ?? $this->postUrn;

        $ch = curl_init(
            'https://api.linkedin.com/v2/socialActions/'
            . rawurlencode($commentUrn)
            . '/comments'
        );
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POSTFIELDS     => json_encode([
                "actor"   => $this->type . $this->person_id,
                "object"  => $commentUrn,
                "message" => ["text" => $this->first_comment],
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'X-Restli-Protocol-Version: 2.0.0',
                'Content-Type: application/json',
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}