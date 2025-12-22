<?php

namespace Modules\AppAnalytics\Services;

use Illuminate\Support\Facades\Http;

class ChartImageGenerator
{
    /**
     * Generate a base64 encoded chart image using QuickChart API
     * 
     * @param array $chartData
     * @param string $chartType (line, bar, pie, etc.)
     * @param array $options
     * @return string|null Base64 encoded image
     */
    public function generateChartImage(array $chartData, string $chartType = 'line', array $options = []): ?string
    {
        try {
            // Use QuickChart.io for server-side chart generation
            $chartConfig = $this->buildChartConfig($chartData, $chartType, $options);
            
            $response = Http::timeout(10)->post('https://quickchart.io/chart', [
                'chart' => json_encode($chartConfig),
                'width' => $options['width'] ?? 600,
                'height' => $options['height'] ?? 300,
                'format' => 'png',
                'backgroundColor' => $options['backgroundColor'] ?? '#ffffff',
            ]);
            
            if ($response->successful()) {
                return 'data:image/png;base64,' . base64_encode($response->body());
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::error('Chart generation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Build Chart.js compatible configuration
     */
    private function buildChartConfig(array $chartData, string $chartType, array $options): array
    {
        $config = [
            'type' => $chartType,
            'data' => [
                'labels' => $chartData['labels'] ?? [],
                'datasets' => $chartData['datasets'] ?? [],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'display' => $options['showLegend'] ?? true,
                        'position' => 'top',
                    ],
                    'title' => [
                        'display' => !empty($options['title']),
                        'text' => $options['title'] ?? '',
                    ],
                ],
            ],
        ];
        
        // Add chart-specific options
        if ($chartType === 'line') {
            $config['options']['scales'] = [
                'y' => [
                    'beginAtZero' => $options['beginAtZero'] ?? true,
                ],
            ];
        }
        
        return $config;
    }
    
    /**
     * Generate a simple trend chart for followers/metrics over time
     */
    public function generateTrendChart(array $data, string $label = 'Metric', array $options = []): ?string
    {
        $chartData = [
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'label' => $label,
                    'data' => array_values($data),
                    'borderColor' => $options['color'] ?? 'rgb(75, 192, 192)',
                    'backgroundColor' => $options['backgroundColor'] ?? 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
        ];
        
        return $this->generateChartImage($chartData, 'line', array_merge([
            'width' => 800,
            'height' => 300,
            'showLegend' => false,
        ], $options));
    }
    
    /**
     * Alternative: Generate chart using local library (if QuickChart fails)
     * This would require installing a package like "asika/php-image-chart"
     */
    public function generateChartImageLocal(array $chartData, string $chartType = 'line'): ?string
    {
        // Implement local chart generation as fallback
        // This is a placeholder - you would use a library like GD or ImageMagick
        return null;
    }
}
