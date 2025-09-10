<?php
namespace App\Filament\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components;
use Filament\Forms\Components\Select as FormsSelect;
use Filament\Forms\Components\TextInput as FormsTextInput;
use Filament\Forms\Components\DatePicker as FormsDatePicker;
use Filament\Forms\Components\Fieldset;

class PetFormSchema
{
    public static function schema(bool $includeCustomerSelect = false): array
    {
        // Fieldset: Identificación básica
        $identification = Fieldset::make('Identificación')
            ->schema(array_filter([
                $includeCustomerSelect
                    ? Select::make('customer_id')
                        ->relationship('customer', 'identification')
                        ->label('Cliente')
                        ->required()
                    : null,

                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                Select::make('species')
                    ->label('Especie')
                    ->options(self::speciesOptions())
                    ->required(),

                Select::make('gender')
                    ->label('Género')
                    ->options(self::genderOptions())
                    ->required(),
            ]))
            ->columns(2); // dos columnas dentro del fieldset

        // Fieldset: Datos médicos / físicos
        $medical = Fieldset::make('Datos médicos')
            ->schema([
                DatePicker::make('birth_date')
                    ->label('Fecha de nacimiento')
                    ->required(),

                TextInput::make('weight')
                    ->label('Peso (kg)')
                    ->numeric(),
            ])
            ->columns(2);

        // Fieldset: Historial y flags
        $extras = Fieldset::make('Historial & estado')
            ->schema([
                KeyValue::make('history')
                    ->keyLabel('Condición')
                    ->valueLabel('Detalles')
                    ->valuePlaceholder('ej.: Diagnosticado con diabetes en 2020')
                    ->addActionLabel('Agregar condición')
                    ->columnSpanFull(),

                Toggle::make('is_alive')
                    ->label('Está vivo')
                    ->default(true)
                    ->required(),
            ]);

        // Retornamos un array de Fieldsets (puedes agregar Cards, Grid, etc.)
        return [
            $identification,
            $medical,
            $extras,
        ];
    }

    public static function speciesOptions(): array
    {
        return [
            'dog' => __('Dog'),
            'cat' => __('Cat'),
            'bird' => __('Bird'),
            'reptile' => __('Reptile'),
            'other' => __('Other'),
        ];
    }

    public static function genderOptions(): array
    {
        return [
            'male' => __('Male'),
            'female' => __('Female'),
            'other' => __('Other'),
        ];
    }
}
