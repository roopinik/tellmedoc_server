<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Filament\Resources\DoctorResource\RelationManagers;
use App\Models\HealthCareUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Resources\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Tabs;


class DoctorResource extends Resource
{
    use Translatable;
    protected static ?string $model = HealthCareUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $label = 'Doctor';
    protected static ?string $navigationLabel = 'Doctors';
    public static ?string $slug = 'doctor';
    protected static ?int $navigationSort = 1;


    public static function canViewAny(): bool
    {
        return auth('filament')->user()->can("doctors.manage");
    }
    public static function getEloquentQuery(): Builder
    {
        $user = auth('filament')->user();
        return parent::getEloquentQuery()->where('client_id', $user->client_id)->whereHas(
            "roles",
            function ($q) {
                $q->where('name', 'doctor');
            }
        );
    }

    public static function form(Form $form): Form
    {
        $user = auth('filament')->user();
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('User Information')
                            ->schema([
                                Forms\Components\FileUpload::make('profile_pic')->directory('profile_pics'),
                                Forms\Components\TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name_translated')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required(fn(string $context): bool => $context === 'create'),

                                Forms\Components\Select::make('gender')->options(
                                    [
                                        "male" => "Male",
                                        "female" => "Female",
                                        "other" => "Other"
                                    ]
                                ),
                                Forms\Components\DatePicker::make('date_of_birth'),
                                Forms\Components\DatePicker::make('working_since'),
                                Forms\Components\TextInput::make('mobile_number')
                                    ->required()
                                    ->prefix("+91")
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('license_id')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('whats_app_number')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Checkbox::make("disabled")->label("Block User")->default(false),
                            ]),
                        Tabs\Tab::make('Configuration')
                            ->schema([
                                Forms\Components\CheckboxList::make("specializations")
                                    ->label("Departments")
                                    ->relationship(titleAttribute: 'name')->columns(2)
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => $record->name),
                                Forms\Components\CheckboxList::make("hospitals")
                                    ->label("Hospitals")
                                    ->relationship(titleAttribute: 'name')->columns(2)
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name),
                                Forms\Components\CheckboxList::make("educations")
                                    ->relationship(titleAttribute: 'name')->columns(2)
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => $record->name),
                                Forms\Components\CheckboxList::make("languages")
                                    ->relationship(titleAttribute: 'name')->columns(2)
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => $record->name),
                                // Forms\Components\TextInput::make('appointment_slot_duration')
                                //     ->label("Per Appointment Duration in Minutes.")
                                //     ->numeric()
                                //     ->visible(fn($record) => $record->client->appointment_type == "timeslot"),

                            ]),
                        Tabs\Tab::make('Schedules')
                            ->schema([
                                Forms\Components\TextInput::make('max_range_appointments')
                                    ->label("Max number of Appointments per Time Range.")
                                    ->numeric()
                                    ->default(10),
                                Forms\Components\Repeater::make("appointment_slots")
                                    ->schema(
                                        [
                                            Forms\Components\Select::make('hospital')
                                                ->options(\App\Models\Hospital::where("client_id", $user->client_id)->get()->pluck("name", "id"))
                                                ->required(),
                                            Forms\Components\Select::make('weekDay')
                                                ->options([
                                                    "Monday" => "Monday",
                                                    "Tuesday" => "Tuesday",
                                                    "Wednesday" => "Wednesday",
                                                    "Thursday" => "Thursday",
                                                    "Friday" => "Friday",
                                                    "Saturday" => "Saturday",
                                                    "Sunday" => "Sunday"
                                                ])
                                                ->required(),
                                            // Forms\Components\Select::make('slot_type')
                                            //     ->options([
                                            //         "morning" => "Morning",
                                            //         "evening" => "Evening",
                                            //     ])
                                            //     ->required(),
                                            Forms\Components\TimePicker::make('startTime'),
                                            Forms\Components\TimePicker::make('endTime'),
                                            // ...($user->client->appointment_type == 'timeslot' ? [
                                            // Forms\Components\TimePicker::make('startTime'),
                                            // Forms\Components\TimePicker::make('endTime')
                                            // ] : []),
                                            // ...($user->client->appointment_type == 'timerange' ? [Forms\Components\TextInput::make('timerange')->visible(fn($record) => $user->client->appointment_type == 'timerange')] : [])
                                        ],
                                    )
                                    ->label("Time Range")
                                    ->columns(4),
                            ]),
                    ]),

                // Forms\Components\TextInput::make('receptionist_whatsapp_number')
                //     ->required()
                //     ->maxLength(255),
                // Forms\Components\Select::make('receptionist_id')
                //     ->required()
                //     ->relationship('receptionist', 'first_name', fn(Builder $query) => $query->whereHas(
                //         "roles",
                //         function ($q) {
                //             $q->where('name', 'receptionist');
                //         }
                //     )),

                // Forms\Components\TextInput::make('offline_slot_duration')
                //     ->hint("in minutes, duration for each appointment slot. default 15 minutes.")
                //     ->numeric(),
                // Forms\Components\TextInput::make('online_appointment_cost')
                //     ->numeric(),
                // Forms\Components\TextInput::make('offline_appointment_cost')
                //     ->numeric(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name'),
                Tables\Columns\TextColumn::make('last_name'),
                Tables\Columns\TextColumn::make('email'),
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
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
