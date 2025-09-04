<?php

namespace App\Filament\TenantManager\Resources\Training\LessonResource\Pages;

use App\Filament\TenantManager\Resources\Training\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLesson extends CreateRecord
{
    protected static string $resource = LessonResource::class;
}
