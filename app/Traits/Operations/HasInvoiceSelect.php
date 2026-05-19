<?php

namespace App\Traits\Operations;

use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use App\Models\Invoice;

trait HasInvoiceSelect
{
    public static function invoiceSelect(): Forms\Components\Select
    {
        return Forms\Components\Select::make('invoice_id')
            ->label('Código de factura')
            ->relationship(
                'invoice',
                'code',
                fn (Builder $query) => $query->where('team_id', Filament::getTenant()?->id)
            )
            ->createOptionForm(fn (Forms\Form $form): Forms\Form => HasInvoiceFormAndTable::buildInvoiceForm($form))
            ->createOptionUsing(function (array $data): int {
                $data['team_id'] = $data['team_id'] ?? Filament::getTenant()?->id;

                $invoice = Invoice::create([
                    'team_id' => $data['team_id'] ?? null,
                    'code' => $data['code'],
                    'amount' => $data['amount'],
                    'is_our' => (bool) ($data['is_our'] ?? false),
                    'issued_date' => $data['issued_date'],
                    'supplier_id' => $data['supplier_id'] ?? null,
                    'data' => $data['data'] ?? null,
                ]);

                return (int) $invoice->getKey();
            })
            ->helperText('Selecciona la factura asociada a esta recepción técnica. Si la factura no existe, puedes crear una nueva desde aquí.')
            ->required();
    }
}
