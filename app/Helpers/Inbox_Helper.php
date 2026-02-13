<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class Inbox_Helper
{
    public static function getInboxCount($brandId = null)
    {
        // Count from inbox
        $inboxQuery = DB::table('inbox')
            ->selectRaw('COUNT(id) as count')
            ->where('is_completed', 0)
            ->where('is_deleted', 0)
            ->where('to_type', 'me');
        
        if (!empty($brandId)) {
            $inboxQuery->where('brand_id', $brandId);
        }
        
        $inboxCount = $inboxQuery->value('count') ?? 0;
        
        // Count from sp_inbox_comments
        $commentQuery = DB::table('inbox_comments')
            ->selectRaw('COUNT(id) as count')
            ->where('is_completed', 0)
            ->where('is_deleted', 0)
            ->where('to_type', 'me');
        
        if (!empty($brandId)) {
            $commentQuery->where('brand_id', $brandId);
        }
        
        $commentCount = $commentQuery->value('count') ?? 0;
        
        // Return sum
        return (int)$inboxCount + (int)$commentCount;
    }
	public static function getInboxCountsByBrands($brandIds = [])
    {
        $counts = [];
        
        if (empty($brandIds)) {
            return $counts;
        }
        
        // Get counts from sp_inbox
        $inboxCounts = DB::table('inbox')
            ->select('brand_id', DB::raw('COUNT(*) as count'))
            ->where('is_completed', 0)
            ->where('is_deleted', 0)
            ->where('to_type', 'me')
            ->whereIn('brand_id', $brandIds)
            ->groupBy('brand_id')
            ->pluck('count', 'brand_id')
            ->toArray();
        
        // Get counts from sp_inbox_comments
        $commentCounts = DB::table('inbox_comments')
            ->select('brand_id', DB::raw('COUNT(*) as count'))
            ->where('is_completed', 0)
            ->where('is_deleted', 0)
            ->where('to_type', 'me')
            ->whereIn('brand_id', $brandIds)
            ->groupBy('brand_id')
            ->pluck('count', 'brand_id')
            ->toArray();
        
        // Combine counts
        foreach ($brandIds as $brandId) {
            $counts[$brandId] = ($inboxCounts[$brandId] ?? 0) + ($commentCounts[$brandId] ?? 0);
        }
        
        return $counts;
    }
}