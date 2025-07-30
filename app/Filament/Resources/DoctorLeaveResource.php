<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorLeaveResource\Pages;
use App\Filament\Resources\DoctorLeaveResource\RelationManagers;
use App\Models\DoctorLeave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;

class DoctorLeaveResource extends Resource
{
    protected static ?string $model = DoctorLeave::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('doctor_id')
                    ->label("Doctor")
                    ->required()
                    ->options(
                        \App\Models\HealthCareUser::
                            where("client_id", auth('filament')->user()->client->id)
                            ->whereHas(
                                "roles",
                                function ($q) {
                                    $q->where('name', 'doctor');
                                }
                            )->get()->pluck("name_translated", "id")
                    )
                    ->required()->placeholder("Select Doctor"),
                Forms\Components\DateTimePicker::make('leave_start')->required()->live(),
                Forms\Components\DateTimePicker::make('leave_end')
                    ->required()
                    ->disabled(fn(Get $get) => $get('leave_start') == null)
                    ->maxDate(fn(Get $get) => substr($get('leave_start'), 0, 10) . " 24:59:00"),
                Forms\Components\Toggle::make("enabled")->default(true)
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('doctor.name_translated')->label("Doctor"),
                Tables\Columns\TextColumn::make('leave_start')->label("Start"),
                Tables\Columns\TextColumn::make('leave_end')->label("End")
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDoctorLeaves::route('/'),
            'create' => Pages\CreateDoctorLeave::route('/create'),
            'edit' => Pages\EditDoctorLeave::route('/{record}/edit'),
        ];
    }
}
