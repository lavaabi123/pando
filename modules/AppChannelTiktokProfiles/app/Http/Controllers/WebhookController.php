<?php
namespace Modules\AppChannelTiktokProfiles\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController
{
    /**
     * Handle webhook verification (GET request)
     * TikTok calls this when you first register the webhook
     */
    public function verify(Request $request)
    {
        $challenge = $request->get('hub.challenge') 
                  ?? $request->get('challenge');
        
        $verifyToken = $request->get('verify_token')
                    ?? $request->get('verify_token');
        
        Log::info('[TikTok Webhook] Verification request', [
            'challenge' => $challenge,
            'verify_token' => $verifyToken,
            'all_params' => $request->all()
        ]);
        
        // Get your verify token from options table
        $expectedToken = get_option('tiktok_webhook_secret', '');
        
        // Verify the token matches
        if ($verifyToken && $verifyToken === $expectedToken) {
            Log::info('[TikTok Webhook] Verification successful');
            return response($challenge, 200)
                ->header('Content-Type', 'text/plain');
        }
        
        Log::warning('[TikTok Webhook] Verification failed', [
            'expected' => $expectedToken,
            'received' => $verifyToken
        ]);
        
        return response('Verification failed', 403);
    }
    
    /**
     * Handle incoming webhooks (POST request)
     */
    public function handle(Request $request)
    {
        try {
            // Log everything for debugging
            Log::info('[TikTok Webhook] Raw request received', [
                'method' => $request->method(),
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'raw_body' => $request->getContent()
            ]);
            
            // Verify signature
            if (!$this->verifySignature($request)) {
                Log::warning('[TikTok Webhook] Invalid signature');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
            
            $payload = $request->all();
            
            // Extract event type (TikTok might use different field names)
            $eventType = $payload['event'] 
                      ?? $payload['event_type'] 
                      ?? $payload['type'] 
                      ?? '';
            
            Log::info('[TikTok Webhook] Processing event', [
                'event_type' => $eventType,
                'payload_keys' => array_keys($payload)
            ]);
            
            // Handle different event types
            switch ($eventType) {
                case 'message.received':
                case 'message_received':
                case 'messaging_webhook':
                    return $this->handleIncomingMessage($payload);
                    
                case 'message.sent':
                case 'message_sent':
                    return $this->handleOutgoingMessage($payload);
                    
                default:
                    Log::info('[TikTok Webhook] Unhandled event', [
                        'type' => $eventType,
                        'payload' => $payload
                    ]);
                    return response()->json(['status' => 'received'], 200);
            }
            
        } catch (\Exception $e) {
            Log::error('[TikTok Webhook] Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Always return 200 to prevent TikTok from retrying
            return response()->json(['status' => 'error_logged'], 200);
        }
    }
    
    /**
     * Verify webhook signature
     */
    protected function verifySignature(Request $request): bool
    {
        // Check different possible header names TikTok might use
        $signature = $request->header('X-TikTok-Signature')
                  ?? $request->header('X-Hub-Signature-256')
                  ?? $request->header('X-Signature');
                  
        $timestamp = $request->header('X-TikTok-Timestamp')
                  ?? $request->header('X-Timestamp');
        
        if (!$signature) {
            Log::warning('[TikTok Webhook] No signature found', [
                'all_headers' => $request->headers->all()
            ]);
            
            // TEMPORARY - For initial testing only
            // TODO: Remove this after confirming webhooks work
            return true;
        }
        
        // Get webhook secret from options table
        $secret = get_option('tiktok_webhook_secret', '');
        
        if (empty($secret)) {
            Log::error('[TikTok Webhook] No webhook secret configured');
            return false;
        }
        
        // Calculate expected signature
        $body = $request->getContent();
        
        // Try different signature methods TikTok might use
        
        // Method 1: SHA256 of body only
        $expectedSignature = hash_hmac('sha256', $body, $secret);
        if (hash_equals($expectedSignature, $signature)) {
            return true;
        }
        
        // Method 2: SHA256 of timestamp + body
        if ($timestamp) {
            $expectedSignature = hash_hmac('sha256', $timestamp . $body, $secret);
            if (hash_equals($expectedSignature, $signature)) {
                return true;
            }
            
            // Method 3: SHA256 of timestamp + '.' + body
            $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $body, $secret);
            if (hash_equals($expectedSignature, $signature)) {
                return true;
            }
        }
        
        Log::warning('[TikTok Webhook] Signature mismatch', [
            'expected' => $expectedSignature,
            'received' => $signature
        ]);
        
        return false;
    }
    
    /**
     * Handle incoming message (user sent message to your account)
     */
    protected function handleIncomingMessage(array $payload)
    {
        try {
            // TikTok webhook structure might vary, handle different formats
            $entry = $payload['entry'][0] ?? $payload;
            $messaging = $entry['messaging'][0] ?? $entry;
            
            // Extract recipient (your TikTok account ID)
            $recipientId = $messaging['recipient']['id'] 
                        ?? $payload['recipient_id']
                        ?? $entry['id']
                        ?? '';
            
            if (empty($recipientId)) {
                Log::warning('[TikTok Webhook] No recipient ID found', [
                    'payload_structure' => array_keys($payload),
                    'entry_structure' => isset($entry) ? array_keys($entry) : [],
                    'messaging_structure' => isset($messaging) ? array_keys($messaging) : []
                ]);
                return response()->json(['status' => 'no_recipient'], 200);
            }
            
            // Find account in YOUR database
            $account = DB::table('accounts')
                ->where('social_network', 'tiktok')
                ->where('pid', $recipientId)
                ->first();
            
            if (!$account) {
                Log::warning('[TikTok Webhook] Account not found', [
                    'recipient_id' => $recipientId,
                    'existing_tiktok_accounts' => DB::table('accounts')
                        ->where('social_network', 'tiktok')
                        ->pluck('pid', 'id')
                        ->toArray()
                ]);
                return response()->json(['status' => 'account_not_found'], 200);
            }
            
            Log::info('[TikTok Webhook] Account found', [
                'account_id' => $account->id,
                'brand_id' => $account->brand_id,
                'username' => $account->username
            ]);
            
            // Extract message data
            $message = $messaging['message'] ?? [];
            $sender = $messaging['sender'] ?? [];
            
            $senderId = $sender['id'] ?? '';
            $messageText = $message['text'] ?? '';
            $messageId = $message['mid'] ?? $message['id'] ?? uniqid('tiktok_');
            $timestamp = $message['timestamp'] ?? time();
            
            // Determine message direction
            $totype = ($senderId == $account->pid) ? 'me' : '';
            
            // Set avatar images
            if ($senderId == $account->pid) {
                $from_image = $account->avatar ?? '';
                $to_image = theme_public_asset('img/default.png');
            } else {
                $from_image = theme_public_asset('img/default.png');
                $to_image = $account->avatar ?? '';
            }
            
            // Prepare inbox data (matching your inbox table structure)
            $inboxData = [
                'user_id' => '1',
                'account_id' => $account->id,
                'post_id' => '',
                'brand_id' => $account->brand_id,
                'team_id' => $account->team_id,
                'conversation_id' => $messaging['conversation_id'] ?? $senderId,
                'media_type' => 'tiktok',
                'inbox_type' => 'Messenger',
                'message' => $messageText,
                'story' => '',
                'shares' => '',
                'attachments' => $this->extractAttachments($message),
                'from_name' => $sender['name'] ?? $sender['username'] ?? 'Unknown',
                'from_user_id' => $senderId,
                'to_name' => $account->name ?? $account->username ?? '',
                'to_type' => $totype,
                'to_user_id' => $account->pid,
                'from_image' => $from_image,
                'to_image' => $to_image,
                'message_id' => $messageId,
                'created_time' => date('Y-m-d H:i:s', is_numeric($timestamp) ? $timestamp : time()),
            ];
            
            // Save to database (upsert logic)
            $exists = DB::table('inbox')
                ->where('message_id', $inboxData['message_id'])
                ->count();
            
            if ($exists) {
                DB::table('inbox')
                    ->where('message_id', $inboxData['message_id'])
                    ->update($inboxData);
                    
                Log::info('[TikTok Webhook] Message updated', [
                    'message_id' => $inboxData['message_id']
                ]);
            } else {
                try {
                    DB::table('inbox')->insert($inboxData);
                    
                    Log::info('[TikTok Webhook] Message inserted', [
                        'message_id' => $inboxData['message_id'],
                        'conversation_id' => $inboxData['conversation_id']
                    ]);
                } catch (\Exception $e) {
                    Log::error('[TikTok Webhook] Insert failed', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::error('[TikTok Webhook] Error processing incoming message', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['status' => 'error'], 200);
        }
    }
    
    /**
     * Handle outgoing message (your account sent message to user)
     */
    protected function handleOutgoingMessage(array $payload)
    {
        // You can implement this later if needed
        Log::info('[TikTok Webhook] Outgoing message received', [
            'payload' => $payload
        ]);
        
        return response()->json(['status' => 'success'], 200);
    }
    
    /**
     * Extract attachment URLs from message
     */
    protected function extractAttachments(array $message): string
    {
        if (empty($message['attachments'])) {
            return '';
        }
        
        foreach ($message['attachments'] as $attachment) {
            // Check for image
            if (!empty($attachment['payload']['url'])) {
                return $attachment['payload']['url'];
            }
            
            // Check for direct URL
            if (!empty($attachment['url'])) {
                return $attachment['url'];
            }
        }
        
        return '';
    }
}