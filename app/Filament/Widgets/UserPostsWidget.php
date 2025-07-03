<?php

namespace App\Filament\Widgets;

use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Post;
use Filament\Widgets\ChartWidget;

class UserPostsWidget extends ChartWidget
{
    protected static ?string $heading = 'Posts Over Time';

    protected function getData(): array
    {

        $userId=auth()->id();

        $data=Trend::query(Post::query()->where('user_id',$userId))
            ->between(now()->subDays(30), now())
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Blog posts',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
