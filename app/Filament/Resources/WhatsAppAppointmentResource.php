<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsAppAppointmentResource\Pages;
use App\Filament\Resources\WhatsAppAppointmentResource\RelationManagers;
use App\Models\Hospital;
use App\Models\WhatsAppAppointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;

class WhatsAppAppointmentResource extends Resource
{
    protected static ?string $model = WhatsAppAppointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Appointment';

    // public static function shouldRegisterNavigation(): bool
    // {
    //     return false;
    // }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy("appointment_date", "desc")->orderBy("appointment_time", "asc")->where("payment_status", "PAYMENT_COMPLETED")->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        $clientId = auth('filament')->user()->client_id;
        return $table
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('patient_name')
                    ->label('Patient Name')->searchable()
                    ->description(
                        function ($record) {
                            $date = Carbon::createFromFormat('Y-m-d H:i', $record->appointment_date . " " . $record->appointment_time)->format('M d Y h:i A');
                            $whatsApp = $record->booking_whatsapp_number;
                            return new HtmlString("<b>Appointment Time: $date</b><br/><b>WhatsApp: $whatsApp</b><br/>");
                        }
                    ),
                Tables\Columns\TextColumn::make('appointment_mode')
                    ->label('Type')->badge()->color(fn(string $state): string => match ($state) {
                        'offline' => 'warning',
                        'online' => 'success',
                    }),
                // Tables\Columns\TextColumn::make('email')
                //     ->label('Email'),
                Tables\Columns\TextColumn::make("created_at"),

            ])
            ->filters([
                Filter::make('appointment_date')
                    ->form([
                        DatePicker::make('start_date')->default(now()),
                        DatePicker::make('end_date')->default(now()),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('appointment_date', '>=', $date),
                            )->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('appointment_date', '<=', $date),
                            );
                        ;

                    })->default(),
                SelectFilter::make("hospital_id")
                    ->options(Hospital::where("client_id", $clientId)->get()->pluck('name', 'id'))

            ], layout: FiltersLayout::AboveContent)
            ->headerActions([
                Tables\Actions\Action::make("New Appointment")
                    ->visible(fn($record) => auth('filament')->user()->can('create.internal.appointment'))
                    ->url(fn($record): string => env("APP_URL") . "wa/appointment/" . auth('filament')->user()->client->uuid . "/new"),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make("Start Conference")->hidden(fn($record) => $record->appointment_mode != "online")->url(fn($record): string => env("JITSIMEETURL") . "appointment" . $record->id . "#config.disableDeepLinking=true"),
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
            'index' => Pages\ListWhatsAppAppointments::route('/'),
            'create' => Pages\CreateWhatsAppAppointment::route('/create'),
            'edit' => Pages\EditWhatsAppAppointment::route('/{record}/edit'),
        ];
    }
}
