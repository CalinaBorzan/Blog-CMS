<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DraftPostResource\Pages;
use App\Filament\Resources\DraftPostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class DraftPostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?string $navigationLabel = 'My Draft Posts';


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'draft')
            ->where('user_id', auth()->id());
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

                            ->unique(table: 'tags', column: 'name')
                            ->relationship('tags', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->unique(table: 'tags', column: 'name', ignoreRecord: true)
                                    ->maxLength(20),
                            ]),
                    ]),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft'=>'Draft',
                        'published'=>'Published',
                    ])
                    ->default('draft')
                    ->required(),

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
                Tables\Columns\TextColumn::make('category.name')->label('Category'),
                BadgeColumn::make('status')->colors(['primary'=>'draft','success'=>'published'])

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')->label('Category')->relationship('category', 'name'),
                Tables\Filters\MultiSelectFilter::make('tags')->label('Tags')->relationship('tags', 'name'),
                Tables\Filters\SelectFilter::make('status')->options(['draft'=>'Draft','published'=>'Published',]),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDraftPosts::route('/'),
            'edit' => Pages\EditDraftPost::route('/{record}/edit'),
            'create' => Pages\CreateDraftPost::route('/create'),
        ];
    }
}
