<?php

namespace App\Filament\TenantManager\Resources\Training\CourseResource\Pages;

use App\Filament\TenantManager\Resources\Training\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;
}
