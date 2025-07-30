<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('appointment_date')
                    ->required(),
                Forms\Components\TimePicker::make('schedule_time')->seconds(false),
                Forms\Components\Select::make('appointment_type')->options(
                    [
                        "visit" => "Visit",
                        "online" => "Online"
                    ]
                ),
                Forms\Components\Select::make('person_id')
                    ->required()
                    ->relationship('person', 'name'),
                Forms\Components\Select::make('doctor_id')
                    ->required()
                    ->relationship('doctor', 'first_name', fn(Builder $query) => $query->whereHas(
                        "roles",
                        function ($q) {
                            $q->where('name', 'doctor');
                        }
                    )),
                Forms\Components\TextInput::make('abha_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('mrn')
                    ->maxLength(255),
                Forms\Components\Select::make('appointment_status')->options(
                    [
                        "pending" => "Pending",
                        "booked" => "Booked",
                        "complete" => "Completed",
                        "failed" => "Failed",
                        "cancelled" => "Cacelled",
                        "onhold" => "On Hold",
                    ]
                ),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('appointment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('schedule_time')
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctor.first_name')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
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
