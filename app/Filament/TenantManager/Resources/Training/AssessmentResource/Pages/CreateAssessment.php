<?php

namespace App\Filament\TenantManager\Resources\Training\AssessmentResource\Pages;

use App\Filament\TenantManager\Resources\Training\AssessmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAssessment extends CreateRecord
{
    protected static string $resource = AssessmentResource::class;
}
