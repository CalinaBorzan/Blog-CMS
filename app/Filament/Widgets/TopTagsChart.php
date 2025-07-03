<?php

namespace App\Filament\Widgets;

use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Tag;
use Filament\Widgets\ChartWidget;

class TopTagsChart extends ChartWidget
{
    protected static ?string $heading = 'Top Tags';

    protected function getData(): array
    {


        $data=Tag::withCount('posts')
            ->orderByDesc('posts_count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Posts per Tag',
                    'data' => $data->pluck('posts_count'),
                    'backgroundColor' => ['#60a5fa', '#34d399', '#facc15', '#f87171', '#c084fc'],
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
