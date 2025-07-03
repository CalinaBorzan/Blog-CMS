<?php

namespace App\Filament\Pages;
use Illuminate\Database\Eloquent\Collection;
use Filament\Pages\Dashboard as BaseDashboard;

use App\Models\Post;
use Filament\Pages\Page;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';


  protected function getHeaderWidgets(): array
{
    return [
        \App\Filament\Widgets\UserPostsWidget::class,
        \App\Filament\Widgets\TopTagsChart::class,

    ];
}



    public function mount(): void
    {
        $this->posts = Post::where('user_id', auth()->id())->latest()->take(5)->get();
    }

    public $posts = [];
}
