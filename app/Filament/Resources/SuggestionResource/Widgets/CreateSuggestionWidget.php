<?php

namespace App\Filament\Resources\SuggestionResource\Widgets;

use App\Models\Suggestion;
use Filament\Forms\Components\RichEditor;
use Filament\Widgets\Widget;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\User;
use Livewire\Attributes\On;
use Asmit\FilamentMention\Forms\Components\RichMentionEditor;

class CreateSuggestionWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected int|string|array $columnSpan = 'full';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'user_id' => auth()->id(),
            'status' => 'new',
            'priority' => 'low',
            'type' => 'other',
        ]);
    }

    public function create(): void
    {
        Suggestion::create($this->form->getState());
        $this->form->fill();
        $this->dispatch('suggestion-created');
    }



    protected static string $view = 'filament.resources.suggestion-resource.widgets.create-suggestion-widget';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Suggestion Details')
                    ->description('Please provide the details of your suggestion')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Select::make('user_id')
                                    ->label('User')
                                    ->options(fn() => User::pluck('name', 'id'))
                                    ->default(auth()->id())
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter the title of your suggestion'),
                                Select::make('type')
                                    ->required()
                                    ->options([
                                        'bug' => 'Bug Report',
                                        'feature' => 'Feature Request',
                                        'improvement' => 'Improvement',
                                        'other' => 'Other',
                                    ])
                                    ->default('other')
                                    ->helperText('Select the type of suggestion')
                                    ->native(false),
                                Select::make('priority')
                                    ->required()
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                    ])
                                    ->default('low')
                                    ->helperText('Set the priority level')
                                    ->native(false),
                            ]),
                        RichMentionEditor::make('description')
                            ->required()
                            ->lookupKey('name')
                            ->placeholder('Describe your suggestion in detail')
                            ->columnSpanFull(),
                    ]),
                // Hidden Fields
            ])
            ->statePath('data');
    }
}