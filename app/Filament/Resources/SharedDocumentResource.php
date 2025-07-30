<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SharedDocumentResource\Pages;
use App\Filament\Resources\SharedDocumentResource\RelationManagers;
use App\Models\SharedDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SharedDocumentResource extends Resource
{
    protected static ?string $model = SharedDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = "Document";
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public static function form(Form $form): Form
    {
        $clientId = auth()->user()->client->uuid;
        return $form
            ->schema([
                Forms\Components\TextInput::make('whatsapp_number')
                    ->required()
                    ->maxLength(15),
                Forms\Components\FileUpload::make('document_path')
                    ->required()
                    ->label("document")
                    ->directory("shared-documents/" . $clientId)->visibility("private"),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('whatsapp_number')->searchable(),
                Tables\Columns\TextColumn::make('created_at'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make("Open")
                    ->url(fn($record): string => env("APP_URL") . "enc/" . str_replace("shared-documents/", "", $record->document_path))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSharedDocuments::route('/'),
            'create' => Pages\CreateSharedDocument::route('/create'),
            'edit' => Pages\EditSharedDocument::route('/{record}/edit'),
        ];
    }
}
