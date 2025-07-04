<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostCommentsRelationManagerResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\PostReactionsRelationManagerResource\RelationManagers\ReactionsRelationManager;
use App\Models\Comment;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use App\Filament\Resources\PostResource;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
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
                                               <strong style="color: #3b82f6;">Title:</strong> ' . e($record->title) . '
                                               </div>'
                                )
                                ),

                            Placeholder::make('category')
                                ->label(false)
                                ->content(fn($record): HtmlString =>
                                new HtmlString(
                                    '<div style="font-size: 1.25rem;">
                                              <strong style="color: #3b82f6;">Category:</strong> ' . e($record->category->name) . '
                                              </div>'
                                )
                                ),

                            Placeholder::make('tags')
                                ->label(false)
                                ->content(fn($record): HtmlString =>
                                new HtmlString(
                                    '<div style="font-size: 1.25rem;">
                                               <strong style="color: #3b82f6;">Tags:</strong> ' . e($record->tags->pluck('name')->implode(', ')) . '
                                                </div>'
                                )
                                ),

                            Placeholder::make('author_name')
                                ->label(false)
                                ->content(fn($record): HtmlString =>
                                new HtmlString(
                                    '<div style="font-size: 1.25rem;">
                                               <strong style="color: #3b82f6;">Author:</strong> ' . e($record->user->name) . '
                                               </div>'
                                )
                                ),

                            Placeholder::make('created_at')
                                ->label(false)
                                ->content(fn($record): HtmlString =>
                                new HtmlString(
                                    '<div style="font-size: 1.25rem;">
                                             <strong style="color: #3b82f6;">Published At:</strong> ' .
                                    e(optional($record->created_at)->format('F j, Y, H:i')) . '
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


            Section::make('React to this Post')
                ->schema([
                    Actions::make([
                        FormAction::make('like')
                            ->label(fn ($record) => '👍 ' . $record->reactions()->where('type', 'like')->count())
                            ->action(function () {
                                $this->handleReaction('like');
                            }),
                        FormAction::make('dislike')
                            ->label(fn ($record) => '👎 ' . $record->reactions()->where('type', 'dislike')->count())
                            ->action(function () {
                                $this->handleReaction('dislike');
                            }),
                    ])
                ])
                ->visible(fn () => auth()->check())
                ->collapsible(),

            Section::make('Add a Comment')
                ->schema([
                    Textarea::make('new_comment')
                        ->label('Comment')
                        ->required()
                        ->maxLength(255)
                        ->live()
                        ->default(''),
                    Actions::make([
                        FormAction::make('save_comment')
                            ->label('Submit Comment')
                            ->action(function () {
                                $formState = $this->form->getState();

                                \Log::info('Form state on submit', ['state' => $formState]);

                                $comment = trim($formState['new_comment'] ?? '');

                                if (empty($comment)) {
                                    Notification::make()
                                        ->title('Comment cannot be empty')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                Comment::create([
                                    'content' => $comment,
                                    'user_id' => auth()->id(),
                                    'post_id' => $this->record->id,
                                ]);

                                Notification::make()
                                    ->title('Comment added successfully')
                                    ->success()
                                    ->send();

                                $this->form->fill(['new_comment' => '']);
                            })
                            ->visible(fn () => auth()->check()),
                    ]),
                ])
                ->collapsible()
                ->visible(function () {

                    return auth()->check();
                }),
        ];
    }
    protected function handleReaction(string $type): void
    {
        if (!in_array($type, ['like', 'dislike'])) {
            Notification::make()
                ->title('Invalid reaction type')
                ->danger()
                ->send();
            return;
        }

        $user = auth()->user();
        $post = $this->record;

        $reaction = $post->reactions()->firstOrNew([
            'user_id' => $user->id,
        ]);

        if ($reaction->exists && $reaction->type === $type) {
            $reaction->delete();
        } else {
            $reaction->type = $type;
            $reaction->save();
        }

        Notification::make()
            ->title("You reacted with: " . ucfirst($type))
            ->success()
            ->send();

        $this->record->refresh();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data')
            ->disabled(false);
    }


    public function getRelationManagers(): array
    {

        return [
            CommentsRelationManager::class,
            ReactionsRelationManager::class,
        ];
    }
}
