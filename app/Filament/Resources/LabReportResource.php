<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabReportResource\Pages;
use App\Filament\Resources\LabReportResource\RelationManagers;
use App\Models\LabReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LabReportResource extends Resource
{
    protected static ?string $model = LabReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $clientId = auth()->user()->client_id;
        return $form
            ->schema([
                Forms\Components\TextInput::make('mobile_number')
                ->label("WhatsApp Number")
                    ->required()
                    ->prefix("+91")
                    ->maxLength(15),
                Forms\Components\TextInput::make('person_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('abha_id')
                    ->maxLength(255),
                // Forms\Components\TextInput::make('person_id')
                //     ->numeric(),
                Forms\Components\FileUpload::make('doc_files')
                    ->directory($clientId.'_lab_reports')
                    ->multiple(),
                Forms\Components\FileUpload::make('image_files')
                    ->image()
                    ->directory($clientId.'_lab_reports')
                    ->multiple(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mobile_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('person_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('abha_id')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListLabReports::route('/'),
            'create' => Pages\CreateLabReport::route('/create'),
            'edit' => Pages\EditLabReport::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
