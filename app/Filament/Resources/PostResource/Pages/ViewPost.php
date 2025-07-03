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


    protected function fixLocalhostUrls(string $content): string
    {
        \Log::info('Before fixLocalhostUrls:', ['content' => $content]);

        $result = preg_replace_callback(
            '/(href|src)="http:\/\/localhost(:\d+)?(\/[^"]*)"/i',
            function ($matches) {
                return $matches[1] . '="http://127.0.0.1:8000' . $matches[3] . '"';
            },
            $content
        );

        \Log::info('After fixLocalhostUrls:', ['result' => $result]);

        return $result;
    }


    protected function convertAttachmentsToImages(string $content): string
    {
        logger('convertAttachmentsToImages called', ['content' => $content]);
        return preg_replace_callback(
            '/<a[^>]+href="([^"]+\.(?:jpg|jpeg|png|gif|webp))"[^>]*>.*?<\/a>/i',
            function ($matches) {
                logger('Processing image URL', ['url' => $matches[1]]);

                $url = $matches[1];
                $parsed = parse_url($url);
                if ($parsed === false) {
                    return '<img src="' . e($url) . '" alt="Image" style="max-width:100%; height:auto;" />';
                }

                if (isset($parsed['host']) && $parsed['host'] === 'localhost') {
                    $newUrl = 'http://127.0.0.1:8000' . ($parsed['path'] ?? '');
                    logger('Replaced localhost with 127.0.0.1', ['newUrl' => $newUrl]);
                    return '<img src="' . e($newUrl) . '" alt="Image" style="max-width:100%; height:auto;" />';
                }

                return '<img src="' . e($url) . '" alt="Image" style="max-width:100%; height:auto;" />';
            },
            $content
        );
    }


    protected function getFormSchema(): array
    {
        \Log::info('>>> getViewSchema() called');

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
                            $this->convertAttachmentsToImages($this->fixLocalhostUrls($record->content)) .
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

