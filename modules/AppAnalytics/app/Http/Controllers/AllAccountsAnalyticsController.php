<?php

namespace Modules\AppAnalytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\AppChannels\Models\Accounts;
use Modules\AppAnalytics\Services\ChartImageGenerator;
use Carbon\Carbon;

class AllAccountsAnalyticsController extends Controller
{
    protected $chartGenerator;
    
    public function __construct(ChartImageGenerator $chartGenerator)
    {
        $this->chartGenerator = $chartGenerator;
    }
    
    /**
     * Export PDF for all accounts of current brand
     */
    public function exportAllAccountsPdf(Request $request)
    {
        // Get current brand/team ID from session or auth
        $teamId = session('team_id') ?? auth()->user()->current_team_id;
        $brandName = session('brand_name') ?? 'Brand';
        
        // Parse date range
        [$since, $until] = \Core::parseDateRange($request);
        
        // Get all active accounts for this team
        $accounts = Accounts::where('team_id', $teamId)
            ->where('status', 1)
            ->orderBy('social_network')
            ->get();
        print_r($accounts);exit;
        if ($accounts->isEmpty()) {
            return back()->with('error', 'No active accounts found for this brand.');
        }
        
        $analyticsData = [];
        
        // Collect analytics for each account
        foreach ($accounts as $account) {
            $social = $account->social_network;
            $module = "AppAnalytics" . ucfirst($social);
            $class = "Modules\\{$module}\\Services\\" . ucfirst($social) . "Analytics";
            
            if (!class_exists($class)) {
                continue;
            }
            
            try {
                $service = app($class);
                $analytics = $service->getAnalyticsData($teamId, $account->id_secure, $since, $until);
                
                if ($analytics['status'] === 'success') {
                    // Generate chart image if data is available
                    $chartImage = $this->generateChartForSocial($social, $analytics);
                    
                    $analyticsData[$social][] = [
                        'account' => $account,
                        'analytics' => $analytics,
                        'chartImage' => $chartImage,
                    ];
                }
            } catch (\Exception $e) {
                \Log::error("Failed to get analytics for {$social} account {$account->id}: " . $e->getMessage());
                continue;
            }
        }
        
        if (empty($analyticsData)) {
            return back()->with('error', 'No analytics data available for the selected date range.');
        }
        
        // Generate brand name for filename
        $brandNameClean = preg_replace('/[^A-Za-z0-9_-]/', '_', $brandName);
        
        // Generate PDF
        $pdf = Pdf::loadView('analytics.export_all_accounts_pdf', [
            'analyticsData' => $analyticsData,
            'startDate' => $since,
            'endDate' => $until,
            'brandName' => $brandName,
            'generatedAt' => now()->format('F d, Y'),
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('enable-local-file-access', true);
        
        $filename = $brandNameClean . '_Analytics_' . now()->format('Y-m') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Generate appropriate chart for each social media platform
     */
    private function generateChartForSocial(string $social, array $analytics): ?string
    {
        try {
            switch ($social) {
                case 'facebook':
                    return $this->generateFacebookChart($analytics);
                    
                case 'instagram':
                    return $this->generateInstagramChart($analytics);
                    
                case 'tiktok':
                    return $this->generateTiktokChart($analytics);
                    
                case 'youtube':
                    return $this->generateYoutubeChart($analytics);
                    
                case 'linkedin':
                    return $this->generateLinkedinChart($analytics);
                    
                default:
                    return null;
            }
        } catch (\Exception $e) {
            \Log::error("Chart generation failed for {$social}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate Facebook followers trend chart
     */
    private function generateFacebookChart(array $analytics): ?string
    {
        $chartData = $analytics['fan_history_chart'] ?? null;
        
        if (!$chartData || empty($chartData['data'])) {
            return null;
        }
        
        return $this->chartGenerator->generateTrendChart(
            $chartData['data'],
            'Followers',
            [
                'color' => 'rgb(59, 89, 152)',
                'backgroundColor' => 'rgba(59, 89, 152, 0.1)',
                'title' => 'Followers Growth',
            ]
        );
    }
    
    /**
     * Generate Instagram followers trend chart
     */
    private function generateInstagramChart(array $analytics): ?string
    {
        $chartData = $analytics['followers_chart'] ?? null;
        
        if (!$chartData || empty($chartData['data'])) {
            return null;
        }
        
        return $this->chartGenerator->generateTrendChart(
            $chartData['data'],
            'Followers',
            [
                'color' => 'rgb(225, 48, 108)',
                'backgroundColor' => 'rgba(225, 48, 108, 0.1)',
                'title' => 'Followers Growth',
            ]
        );
    }
    
    /**
     * Generate TikTok followers trend chart
     */
    private function generateTiktokChart(array $analytics): ?string
    {
        $chartData = $analytics['followers_chart'] ?? null;
        
        if (!$chartData || empty($chartData['data'])) {
            return null;
        }
        
        return $this->chartGenerator->generateTrendChart(
            $chartData['data'],
            'Total Followers',
            [
                'color' => 'rgb(0, 0, 0)',
                'backgroundColor' => 'rgba(0, 0, 0, 0.1)',
                'title' => 'Followers Growth',
            ]
        );
    }
    
    /**
     * Generate YouTube subscribers trend chart
     */
    private function generateYoutubeChart(array $analytics): ?string
    {
        $chartData = $analytics['subscribers_chart'] ?? null;
        
        if (!$chartData || empty($chartData['data'])) {
            return null;
        }
        
        return $this->chartGenerator->generateTrendChart(
            $chartData['data'],
            'Subscribers Gained',
            [
                'color' => 'rgb(255, 0, 0)',
                'backgroundColor' => 'rgba(255, 0, 0, 0.1)',
                'title' => 'Subscribers Growth',
            ]
        );
    }
    
    /**
     * Generate LinkedIn followers trend chart
     */
    private function generateLinkedinChart(array $analytics): ?string
    {
        $chartData = $analytics['followers_chart'] ?? null;
        
        if (!$chartData || empty($chartData['data'])) {
            return null;
        }
        
        return $this->chartGenerator->generateTrendChart(
            $chartData['data'],
            'Followers',
            [
                'color' => 'rgb(0, 119, 181)',
                'backgroundColor' => 'rgba(0, 119, 181, 0.1)',
                'title' => 'Followers Growth',
            ]
        );
    }
}
