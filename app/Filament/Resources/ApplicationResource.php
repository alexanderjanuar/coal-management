<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;

use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-tablet';

    protected static ?string $navigationGroup = 'Master Data';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Application Detail')
                    ->description('Basic information about the application')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Application Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('app_url')
                            ->label('Application URL')
                            ->maxLength(255)
                            ->url()
                            ->helperText('The base URL where your application is hosted'),
                        FileUpload::make('logo')
                            ->label('Application Logo')
                            ->image()
                            ->preserveFilenames(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    Tables\Columns\ImageColumn::make('logo'),
                ]),

            ])
            ->filters([
                //
            ])
            ->actions([
            ])
            ->contentGrid([
                'md' => 2,
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
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'view' => Pages\ViewApplication::route('/{record}'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
