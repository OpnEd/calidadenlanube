<?php

namespace App\Filament\CustomerPanel\Pages;

use Filament\Pages\Page;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use App\Models\Customer;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class CustomerAutoRegister extends \Filament\Pages\Dashboard implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public $teams = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->teams = \App\Models\Team::all();
    }

    public function getHeading(): string
    {
        return __('Registro de Clientes Nuevos');
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Medium;
    }

    public function form(Form $form): Form
    {

        return $form
            ->schema([

                Wizard::make([
                    Wizard\Step::make('Cliente')
                        ->icon('phosphor-user')
                        ->schema([
                            Forms\Components\Select::make('team_id')
                                ->label('Compañía / Sede')
                                ->searchable()
                                ->options(
                                    Team::all()->mapWithKeys(function ($team) {
                                        return [$team->id => "{$team->name} ({$team->address})"];
                                    })->toArray()
                                )
                                ->required(),
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('identification')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('address')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phonenumber')
                                ->tel()
                                ->required()
                                ->maxLength(255),
                        ]),
                    Wizard\Step::make('Mascotas')
                        ->icon('phosphor-paw-print')
                        ->schema([
                            Forms\Components\Repeater::make('data')
                                ->label('Mascotas')
                                ->schema([
                                    Forms\Components\Select::make('data_type')
                                        ->label('Tipo de dato')
                                        ->options([
                                            'pet' => 'Mascota',
                                        ])
                                        ->default('pet')
                                        ->required()
                                        ->hidden(),
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nombre')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\Select::make('species')
                                        ->label('Especie')
                                        ->options([
                                            'dog' => 'Perro',
                                            'cat' => 'Gato',
                                            'bird' => 'Ave',
                                            'reptile' => 'Reptil',
                                            'other' => 'Otro',
                                        ])
                                        ->default('dog')
                                        ->required(),
                                    Forms\Components\Select::make('gender')
                                        ->label('Género')
                                        ->options([
                                            'male' => 'Macho',
                                            'female' => 'Hembra',
                                            'other' => 'Otro',
                                        ])
                                        ->default('male')
                                        ->required(),
                                    Forms\Components\DatePicker::make('birth_date')
                                        ->label('Fecha de nacimiento')
                                        ->required(),
                                    Forms\Components\TextInput::make('weight')
                                        ->label('Peso')
                                        ->numeric()
                                        ->required(),
                                ])
                                ->columns(1)
                        ]),
                ])->submitAction(new HtmlString(Blade::render(<<<BLADE
                            <x-filament::button
                                type="submit"
                                size="sm"
                            >
                                Guardar
                            </x-filament::button>
                        BLADE)))
            ])
            ->statePath('data'); // <-- Vincula el formulario a $data
    }

    public function store()
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            // Guardar el cliente
            $customer = new Customer();
            $customer->team_id = $data['team_id'];
            $customer->name = $data['name'];
            $customer->identification = $data['identification'];
            $customer->address = $data['address'];
            $customer->email = $data['email'];
            $customer->phonenumber = $data['phonenumber'];
            $customer->save();

            // Guardar mascotas si existen en el repeater
            if (!empty($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $item) {
                    if (($item['data_type'] ?? null) === 'pet') {
                        $pet = new \App\Models\Pet();
                        $pet->customer_id = $customer->id;
                        $pet->name = $item['name'] ?? null;
                        $pet->species = $item['species'] ?? null;
                        $pet->gender = $item['gender'] ?? null;
                        $pet->birth_date = $item['birth_date'] ?? null;
                        $pet->weight = $item['weight'] ?? null;
                        $pet->save();
                    }
                }
            }

            DB::commit();

            // Limpiar el formulario
            $this->form->fill([]);
            $this->data = [];

            Notification::make()
                ->title('Cliente y mascotas registrados exitosamente')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error al registrar el cliente o mascotas')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    protected static ?string $navigationIcon = 'phosphor-user-plus';
    protected static ?string $navigationLabel = 'Registrarse';
    protected static ?string $slug = 'registro-clientes';
    protected static string $view = 'filament.customer-panel.pages.customer-auto-register';
}
