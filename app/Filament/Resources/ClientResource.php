<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Tabs;
class ClientResource extends Resource
{
    use Translatable;
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Client Information')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('splash_screen')->directory('site_uploads'),
                                Forms\Components\FileUpload::make('logo_path')->directory('site_uploads'),
                                Forms\Components\TextInput::make('phone_number')
                                    ->tel()
                                    ->maxLength(255)
                                    ->default(null),
                                Forms\Components\TextInput::make('receptionist_contact')
                                    ->tel()
                                    ->maxLength(255)
                                    ->default(null),
                                Forms\Components\Select::make('health_care_type')->options(
                                    [
                                        "hospital" => "Hospital",
                                        "clinic" => "Clinic"
                                    ]
                                )->label("Health Care Type")->default("hospital")
                                    ->live(),
                                Forms\Components\Select::make('message_type')
                                    ->options([
                                        'message' => 'Message',
                                        'chatflow' => 'Chat Flow'
                                    ])
                                    ->default('message')
                            ]),
                        Tabs\Tab::make('Specialities')
                            ->schema([
                                Forms\Components\CheckboxList::make("specializations")
                                    ->label("Departments")
                                    ->relationship(titleAttribute: 'name')->columns(2)
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => $record->name),

                            ]),
                        Tabs\Tab::make('Configuration')
                            ->schema([
                                Forms\Components\Select::make('subscription_plan')
                                    ->options([
                                        'basic' => 'Basic',
                                        'advanced' => 'Advanced'
                                    ])
                                    ->default('basic')
                                    ->required(),
                                Forms\Components\Select::make('notification_mode')
                                    ->options([
                                        'sms' => 'SMS',
                                        'whatsapp' => 'WhatsApp'
                                    ])
                                    ->default('sms')
                                    ->required(),
                                Forms\Components\Checkbox::make('force_reschedule')
                                    ->label("Force Reschedule for Missed Appointments")
                                    ->default(false),
                                Forms\Components\Checkbox::make('notify_appointment_booking')
                                    ->label('Notify on Appointment Booking')
                                    ->default(true)
                                    ->helperText('Send notifications when new appointments are booked'),
                                Forms\Components\Checkbox::make("enable_slot_types")->label("Morning Evening Picker")->default(true),
                                Forms\Components\TextInput::make('domain')
                                    ->maxLength(255),
                                Forms\Components\Checkbox::make('collect_patient_id')->default(false)->label("Collect Patient ID"),
                                Forms\Components\Select::make('appointment_type')->options(
                                    [
                                        "timerange" => "Time Range",
                                        "timeslot" => "Time Slot"
                                    ]
                                )->label("Appointment Type")
                                    ->hint("Default Time Range")
                                    ->default("timerange"),
                                Forms\Components\Select::make('priority')->options(
                                    [
                                        "1" => "Prebooked Appointment Show First",
                                        "2" => "No Priority + Time Sort",
                                        "3" => "No Priority",
                                    ]
                                )->label("Queue Priority")
                                    ->hint("Default: show prebooked appointment first in queue.")
                                    ->default("3"),
                                Forms\Components\Select::make('reminder_type')->options(
                                    [
                                        "0" => "Disabled",
                                        "1" => "Daily Appointments",
                                        "2" => "30 Minutes Before",
                                        "3" => "All Remainders (Daily + 30 Min)",
                                    ]
                                )->default(0),
                            ]),
                        Tabs\Tab::make('Theme')
                            ->schema([
                                Forms\Components\ColorPicker::make("primary_color"),
                                Forms\Components\ColorPicker::make("secondary_color"),
                                Forms\Components\ColorPicker::make("accent_color"),
                            ]),
                        Tabs\Tab::make('Payment')
                            ->schema([
                                Forms\Components\Checkbox::make("enable_payment")->label("Enable Payments For Appointments"),
                                Forms\Components\TextInput::make('rp_key_id')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('rp_secret')
                                    ->maxLength(255),
                            ]),
                        Tabs\Tab::make('WhatsApp')
                            ->schema([
                                Forms\Components\TextInput::make('whatsapp_uuid')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('whatsapp_token'),
                                Forms\Components\Textarea::make('whatsapp_header')->label("Message Header")->default(""),
                                Forms\Components\Textarea::make('whatsapp_header_kn')->label("Message Header Kannada")->default(""),
                                Forms\Components\Textarea::make('whatsapp_footer')->label("Message Footer")->default(""),
                                Forms\Components\Textarea::make('whatsapp_footer_kn')->label("Message Footer Kannada")->default(""),
                                Forms\Components\Textarea::make('appointment_instructions')->label("Appointment Instructions")->default(""),
                                Forms\Components\Textarea::make('appointment_instructions_kn')->label("Appointment Instructions Kannada")->default(""),
                                Forms\Components\TextInput::make('flow_template_id')->default("telmedoc_appointment_flow")->label("Flow Template"),
                                Forms\Components\TextInput::make('flow_template_id_kn')->default("telmedoc_appointment_flow_kn")->label("Flow Template Kannada"),
                            ]),
                        Tabs\Tab::make('SMS')
                            ->schema([
                                Forms\Components\TextInput::make('msg91_key')->label("MSG91 Key"),
                                Forms\Components\TextInput::make('min_30_rem_templtid')->label("30 Minute Reminder Template ID"),
                                Forms\Components\TextInput::make('daily_rem_templtid')->label("Daily Reminder Template ID"),
                                Forms\Components\TextInput::make('follow_up_templtid')->label("Follow UP Template ID"),
                                Forms\Components\TextInput::make('cancel_templtid')->label("Cancel Template ID"),
                                Forms\Components\TextInput::make('reschedule_templtid')->label("Reschedule Template ID"),
                            ])
                    ]),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('receptionist_contact')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
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
