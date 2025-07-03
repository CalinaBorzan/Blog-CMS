<?php

namespace App\Filament\Resources\PostResource\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\URL;
use App\Filament\Resources\PostResource;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Form;
use Illuminate\Support\HtmlString;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;

    protected string $localBaseUrl = 'http://127.0.0.1:8000';

    protected function fixUrlsAndConvertImages(string $content): string
    {
        $content = preg_replace_callback(
            '/<a[^>]+href="([^"]+\.(?:jpg|jpeg|png|gif|webp))"[^>]*>.*?<\/a>/i',
            function ($matches) {
                $url = $matches[1];
                $parsed = parse_url($url);
                if ($parsed && isset($parsed['host']) && $parsed['host'] === 'localhost') {
                    $url = $this->localBaseUrl . ($parsed['path'] ?? '');
                }
                return '<img src="' . e($url) . '" alt="Image" style="max-width:100%; height:auto;" />';
            },
            $content
        );

        $content = preg_replace_callback(
            '/(href|src)="http:\/\/localhost(:\d+)?(\/[^"]*)"/i',
            fn($matches) => $matches[1] . '="' . $this->localBaseUrl . $matches[3] . '"',
            $content
        );

        return $content;
    }



    protected function getFormSchema(): array
    {

        return [
            Section::make('Post Details')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('title')
                                ->label(false)
                                ->content(fn($record): HtmlString =>
                                new HtmlString(
                                    '<div style="font-size: 1.25rem;">
                                           <strong style="color: #FBBF24;">Title:</strong> ' . e($record->title) . '
                                           </div>'
                                )
                                ),


                            Placeholder::make('category')
                                ->label(false)
                                ->content(fn($record): HtmlString =>
                                new HtmlString(
                                    '<div style="font-size: 1.25rem;">
                                          <strong style="color: #FBBF24;">Category:</strong> ' . e($record->category->name) . '
                                          </div>'
                                 )
                                ),

                            Placeholder::make('tags')
                                ->label(false)
                                ->content(fn($record): HtmlString =>
                                new HtmlString(
                                    '<div style="font-size: 1.25rem;">
                                           <strong style="color: #FBBF24;">Tags:</strong> ' . e($record->tags->pluck('name')->implode(', ')) . '
                                            </div>'
                                )
                                ),

                            Placeholder::make('author_name')
                                ->label(false)
                                ->content(fn($record): HtmlString =>
                                new HtmlString(
                                    '<div style="font-size: 1.25rem;">
                                           <strong style="color: #FBBF24;">Author:</strong> ' . e($record->user->name) . '
                                           </div>'
                                )
                                ),
                        ]),
                ])
                ->collapsible(),

            Section::make('Content')
                ->schema([
                    Placeholder::make('content')
                        ->label(false)
                        ->content(fn($record) => new HtmlString(
                            '<div style="font-size: 1.125rem; line-height: 1.6;">' .
                            $this->fixUrlsAndConvertImages($record->content) .
                            '</div>'
                        )),
                ])
                ->collapsible(),
        ];
    }
    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

}

