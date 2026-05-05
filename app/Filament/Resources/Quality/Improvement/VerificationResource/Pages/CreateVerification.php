<?php

namespace App\Filament\Resources\Quality\Improvement\VerificationResource\Pages;

use App\Filament\Resources\Quality\Improvement\VerificationResource;
use App\Models\Quality\Improvement\Plan;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateVerification extends CreateRecord
{
    protected static string $resource = VerificationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Filament::getTenant()?->id;
        $data['verified_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var \App\Models\Quality\Improvement\Verification $verification */
        $verification = $this->record;

        $status = match ($verification->result) {
            'efectivo' => 'cerrado',
            'parcial' => 'en_verificacion',
            'no_efectivo' => 'reabierto',
            default => null,
        };

        if ($status) {
            Plan::query()
                ->whereKey($verification->plan_id)
                ->update([
                    'status' => $status,
                    'closed_at' => $status === 'cerrado' ? now() : null,
                ]);
        }
    }
}

