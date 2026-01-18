<?php

namespace App\Services;

use App\Models\Post;
use App\Models\CalendarNote;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarEventService
{
    /**
     * Get calendar events with counts for compose calendar
     * Notes are NOT returned as events - they will be rendered as dots separately
     *
     * @param array $filters
     * @return array
     */
    public function getEventsCount($filters = [])
    {
        $teamId = $filters['team_id'] ?? session('team_id');
        $brandId = session('brand_id');
        
        // Subquery to get the ID of the most recent post per grouping_data
        $subquery = Post::select(\DB::raw('MAX(id) as id'))
            ->where('brand_id', $brandId);
        
        // Apply date range filter
        if (!empty($filters['start']) && !empty($filters['end'])) {
            $subquery->whereBetween('time_post', [
                strtotime($filters['start']), 
                strtotime($filters['end'])
            ]);
        }
        
        // Dynamic filter by status
        if (!empty($filters['status']) && $filters['status'] !== '-1') {
            $subquery->where('status', $filters['status']);
        }
        
        // Dynamic filter by module_name (social network)
        if (!empty($filters['module_name'])) {
            $subquery->where('module', $filters['module_name']);
        }
        
        // Dynamic filter by campaign
        if (!empty($filters['campaign'])) {
            $subquery->where('campaign', $filters['campaign']);
        }
        
        // Dynamic filter by label
        if (!empty($filters['label'])) {
            $labels = is_array($filters['label']) ? $filters['label'] : [$filters['label']];
            $subquery->where(function($q) use ($labels) {
                foreach ($labels as $label) {
                    $q->orWhereJsonContains('labels', (int)$label);
                }
            });
        }
        
        $subquery->groupBy('grouping_data');
        
        // Main query to get full post data
        $query = Post::with('account')
            ->whereIn('id', $subquery)
            ->orderBy('time_post', 'DESC');
        
        // Get posts
        $posts = $query->get();
        
        // Group by date
        $groupedByDate = $posts->groupBy(function($post) {
            return date('Y-m-d', $post->time_post);
        });
        
        // Transform to events
        $events = $groupedByDate->map(function($postsOnDate, $date) {
            $postCount = $postsOnDate->count();
            
            // Get comma-separated list of grouping_data
            $groupingDataList = $postsOnDate->pluck('grouping_data')
                ->filter()
                ->implode(',');
            
            return [
                'title'           => $postCount . ' post' . ($postCount > 1 ? 's' : ''),
                'start'           => $date,
                'backgroundColor' => '#e3f2fd',
                'borderColor'     => '#2196f3',
                'textColor'       => '#1976d2',
                'extendedProps'   => [
                    'post_count'      => $postCount,
                    'date'            => $date,
                    'grouping_data'   => $groupingDataList,
                ],
            ];
        })->values();
        
        return $events->toArray();
    }
    
    /**
     * Get calendar notes grouped by date for tooltip display
     *
     * @param string $start
     * @param string $end
     * @return array - Returns array keyed by date with note details
     */
    public function getCalendarNotesGrouped($start, $end)
    {
        $notes = CalendarNote::with('user')
            ->where('user_id', Auth::id())
            ->where('brand_id', session('brand_id'))
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Group notes by date
        $groupedNotes = [];
        
        foreach ($notes as $note) {
            $dateKey = $note->date->format('Y-m-d');
            
            if (!isset($groupedNotes[$dateKey])) {
                $groupedNotes[$dateKey] = [
                    'date' => $dateKey,
                    'count' => 0,
                    'notes' => []
                ];
            }
            
            $groupedNotes[$dateKey]['count']++;
            $groupedNotes[$dateKey]['notes'][] = [
                'id' => $note->id,
                'text' => $note->notes,
                'user' => $note->user->name ?? 'Unknown',
                'created_at' => $note->created_at->format('M d, Y H:i A')
            ];
        }
        
        return $groupedNotes;
    }
    
    /**
     * Get all events (posts only) for calendar
     * Notes are returned separately for dot rendering
     *
     * @param array $filters
     * @return array
     */
    public function getAllEvents($filters = [])
    {
        return $this->getEventsCount($filters);
    }
}