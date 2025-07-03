<?php

namespace App\Filament\Resources;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Model;

use App\Models\Category;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PHPUnit\Util\Filter;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-pencil';


    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Post::class);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', Post::class);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update', $record);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('delete', $record);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user','category','tags']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->maxLength(20)
                    ->required(),

                Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(20)
                                    ->unique(table: 'categories', column: 'name', ignoreRecord: true),
                            ]),

                        Forms\Components\MultiSelect::make('tags')
                            ->label('Tags')
                            ->required()
                            ->nestedRecursiveRules([
                            'min:3',
                            'max:255',
                        ])
                            ->unique(table: 'tags', column: 'name')
                            ->relationship('tags', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->unique(table: 'tags', column: 'name', ignoreRecord: true)
                                    ->maxLength(20),
                            ]),
                    ]),

                Forms\Components\RichEditor::make('content')

                    ->required(),

                Forms\Components\Placeholder::make('author_name')
                    ->label('Author')
                    ->content(fn(?Post $record) => $record->user->name ?? 'Unknown'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Title') ->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime(),
                Tables\Columns\TextColumn::make('user.name')->label('Author')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')->label('Category')->relationship('category', 'name'),
                Tables\Filters\MultiSelectFilter::make('tags')->label('Tags')->relationship('tags', 'name'),
                Tables\Filters\Filter::make('my_posts')
                ->label('My Posts')
                ->query(fn(Builder $query):Builder =>
                         $query->where('user_id',auth()->id())
                )
                ->toggle()
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Post $record): bool => auth()->user()->can('update', $record)),

                Tables\Actions\ViewAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Post $record): bool => auth()->user()->can('delete', $record)),
            ]);

    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {    \Log::info('PostResource getPages called', ['pages' => ['view' => Pages\ViewPost::class]]);

        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
            'view'=>Pages\ViewPost::route('/{record}'),
        ];
    }
}
