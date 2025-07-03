<?php

namespace App\Filament\Resources\PostResource\Pages;


use App\Filament\Resources\PostResource;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;
use Nette\Utils\Html;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;


    protected function getFormSchema(): array
    {
        return [
            Placeholder::make('title')
            ->label('Title')
            ->content(fn($record): string => $record->title),

            Html::make('content')
            ->label('Content')
            ->html(fn($record): string => $record->content),

            Placeholder::make('author_name')
            ->label('Author')
            ->content(fn($record): string => $record->user->name),

            Placeholder::make('category')
            ->label('Category')
            ->content(fn($record): string => $record->category->name),

            Placeholder::make('tags')
            ->label('Tags')
            ->content(fn($record): string => $record->tags->pluck('name')->implode(', ')),




        ];

    }


}
