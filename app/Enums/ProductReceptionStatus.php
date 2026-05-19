<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum ProductReceptionStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pending = 'in_progress';
    case Realizada = 'done';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'En proceso',
            self::Realizada => 'Completada',
            self::Rejected => 'Rechazada',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Realizada => 'success',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'phosphor-battery-charging',
            self::Realizada => 'phosphor-check-circle',
            self::Rejected => 'phosphor-x-circle',
        };
    }
}
