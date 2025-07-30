<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TelevisionResource\Pages;
use App\Models\HealthCareUser;
use App\Models\Hospital;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class TelevisionResource extends Resource
{
    protected static ?string $model = HealthCareUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-tv';

    protected static ?string $label = 'Television';
    protected static ?string $navigationLabel = 'Televisions';
    public static ?string $slug = 'television';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return true; // You can implement proper authorization logic here later
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth('filament')->user();
        return parent::getEloquentQuery()->where('client_id', $user->client_id)->whereHas(
            "roles",
            function ($q) {
                $q->where('name', 'device.television');
            }
        );
    }

    public static function form(Form $form): Form
    {
        $user = auth('filament')->user();
        
        return $form
            ->schema([
                Forms\Components\FileUpload::make('profile_pic')->directory('profile_pics'),
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('gender')->options(
                    [
                        "male" => "Male",
                        "female" => "Female",
                        "other" => "Other"
                    ]
                ),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('mobile_number')
                    ->required()
                    ->prefix("+91")
                    ->maxLength(255),
                Forms\Components\Select::make('hospital_id')
                    ->label('Hospital')
                    ->options(
                        Hospital::where('client_id', $user->client_id)
                            ->get()
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),
                Forms\Components\Checkbox::make("disabled")->label("Block User")->default(false),
                
                // Global Television Settings - only visible in edit mode
                Forms\Components\Section::make('Television Display Settings')
                    ->schema([
                        Forms\Components\TextInput::make('television_configuration.footer_scroll_text')
                            ->label('Footer Scroll Text')
                            ->placeholder('Enter scrolling text for footer')
                            ->maxLength(500),
                        Forms\Components\FileUpload::make('television_configuration.header_image')
                            ->label('Header Image')
                            ->image()
                            ->disk('public') 
                            ->directory('television_uploads')
                            ->maxSize(2048)
                            ->helperText('Upload header image for television display'),
                        Forms\Components\FileUpload::make('television_configuration.side_banner')
                            ->label('Side Banner')
                            ->image()
                            ->disk('public')
                            ->directory('television_uploads')
                            ->maxSize(2048)
                            ->helperText('Upload side banner image for television display'),
                    ])
                    ->columns(1)
                    ->visible(fn (string $context): bool => $context === 'edit')
                    ->collapsible(),
                
                // Doctor-Hospital Configurations - only visible in edit mode
                Forms\Components\Section::make('Doctor-Hospital Configurations')
                    ->schema([
                        Forms\Components\Repeater::make('television_configuration.doctors')
                            ->label('Doctor-Hospital Pairs')
                            ->schema([
                                Forms\Components\Select::make('doctor_id')
                                    ->label('Doctor')
                                    ->options(
                                        HealthCareUser::where('client_id', $user->client_id)
                                            ->whereHas('roles', function ($q) {
                                                $q->where('name', 'doctor');
                                            })
                                            ->get()
                                            ->pluck('first_name', 'id')
                                    )
                                    ->required()
                                    ->searchable(),
                                Forms\Components\Select::make('hospital_id')
                                    ->label('Hospital')
                                    ->options(
                                        Hospital::where('client_id', $user->client_id)
                                            ->get()
                                            ->pluck('name', 'id')
                                    )
                                    ->required()
                                    ->searchable(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['doctor_id'], $state['hospital_id']) 
                                    ? 'Doctor: ' . (HealthCareUser::find($state['doctor_id'])?->first_name ?? 'Unknown') . ' - Hospital: ' . (Hospital::find($state['hospital_id'])?->name ?? 'Unknown')
                                    : null
                            ),
                    ])
                    ->visible(fn (string $context): bool => $context === 'edit')
                    ->collapsible(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name'),
                Tables\Columns\TextColumn::make('last_name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('hospital.name')
                    ->label('Hospital'),
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
            'index' => Pages\ListTelevisions::route('/'),
            'create' => Pages\CreateTelevision::route('/create'),
            'edit' => Pages\EditTelevision::route('/{record}/edit'),
        ];
    }
} 