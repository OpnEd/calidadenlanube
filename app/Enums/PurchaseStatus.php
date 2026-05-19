<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PurchaseStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case InProgress = 'in progress';
    case Ready = 'ready';
    case Dispatched = 'dispatched';
    case Delivered = 'delivered';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Confirmed => 'Confirmado',
            self::InProgress => 'En progreso',
            self::Ready => 'Listo',
            self::Dispatched => 'Despachado',
            self::Delivered => 'Entregado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'danger',
            self::Confirmed => 'primary',
            self::InProgress => 'info',
            self::Ready => 'warning',
            self::Dispatched => 'gray',
            self::Delivered => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'phosphor-clock',
            self::Confirmed => 'phosphor-check',
            self::InProgress => 'phosphor-arrows-counter-clockwise',
            self::Ready => 'phosphor-package',
            self::Dispatched => 'phosphor-truck',
            self::Delivered => 'phosphor-seal-check',
        };
    }
}
