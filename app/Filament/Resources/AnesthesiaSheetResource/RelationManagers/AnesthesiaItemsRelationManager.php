<?php

namespace App\Filament\Resources\AnesthesiaSheetResource\RelationManagers;

use App\Models\Inventory;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Forms\Set;

class AnesthesiaItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'anesthesiaItems';
    protected static ?string $title = 'Ítems de Anestesia';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('phase')
                    ->label(__('clinic.phase'))
                    ->options([
                        'pre_anesthesia' => 'Pre Anesthesia',
                        'intraoperative' => 'Intraoperative',
                        'post_anesthesia' => 'Post Anesthesia',
                    ])
                    ->default('pre_anesthesia')
                    ->required(),
                Forms\Components\Select::make('inventory_id')
                    ->label(__('clinic.product_name'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship(
                        'inventory',           // nombre de la relación en el modelo AnesthesiaSheetItem
                        'product_name',                  // la columna "display" (la reemplazamos después con ->getOptionLabel)
                        function ($query) {
                            $query
                                ->whereHas('product', fn($q) => $q->where('is_mce', true))
                                ->with('product');  // cargamos product para el label
                        }
                    )
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get) {

                        // Get related values

                        $record = $this->getOwnerRecord();
                        $inventory = Inventory::find($state);
                        $petWeight = optional(optional($record)->pet)->weight ?? 0;
                        $drugConcentration = optional(optional($inventory)->product)->drug_concentration ?? 1;
                        $dosePerKg = optional(optional($inventory)->product)->recommended_dose ?? 1;

                        if ($petWeight && $drugConcentration && $dosePerKg) {
                            $result = ($dosePerKg * $petWeight) / $drugConcentration;
                            $set('dose_per_kg', $dosePerKg);
                            $set('dose_measure', $result);
                            $set('dose_measure_unit', optional($inventory)->product->pharmacheutical_form === 'Tableta' ? 'tab' : 'ml');
                        }
                    })
                    ->live(),
                Forms\Components\TextInput::make('dose_per_kg')
                    ->label(__('clinic.dose'))
                    ->default(0)
                    ->required()
                    ->readonly(),
                Forms\Components\TextInput::make('dose_measure')
                    ->label(__('clinic.dose_measure'))
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('dose_measure_unit')
                    ->label(__('clinic.dose_measure_unit'))
                    ->placeholder('***')
                    ->readOnly()
                    ->required()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('inventory_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('phase')
                    ->label(__('clinic.phase')),
                Tables\Columns\TextColumn::make('inventory.product_name')
                    ->label(__('clinic.product_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dose_per_kg')
                    ->label(__('clinic.dose'))
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextInputColumn::make('dose_measure')
                    ->label(__('clinic.dose_measure')),
                Tables\Columns\TextColumn::make('dose_measure_unit')
                    ->label(__('clinic.dose_measure_unit')),
                Tables\Columns\TextColumn::make('total_dose_mg')
                    ->label(__('clinic.total_dose_mg'))
                    ->getStateUsing(function ($record) {
                        $concentration = optional(optional($record->inventory)->product)->drug_concentration ?? 0;
                        $doseMeasure = $record->dose_measure ?? 0;
                        return $concentration * $doseMeasure;
                    })
                    ->formatStateUsing(fn($state) => number_format($state, 2)),
                Tables\Columns\TextColumn::make('administration_route')
                    ->label(__('clinic.administration_route')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('clinic.attach_anestesia_sheet_item'))
                    ->modalHeading(__('clinic.attach_anestesia_sheet_item'))
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
