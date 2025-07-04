<?php

namespace App\Filament\Pages;
use App\Filament\Widgets\TopTagsChart;
use App\Filament\Widgets\UserPostsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

use App\Models\Post;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';


  protected function getHeaderWidgets(): array
{
    return [
        UserPostsWidget::class,
        TopTagsChart::class,

    ];
}

    public function mount(): void
    {
        $this->posts = Post::where('user_id', auth()->id())->latest()->get();
    }

    public $posts = [];
}
