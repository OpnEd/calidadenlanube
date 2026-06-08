<?php

namespace App\Filament\Resources\Quality\Training\EnrollmentResource\Pages;

use App\Filament\Resources\Quality\Training\EnrollmentResource;
use App\Services\Quality\TrainingService;
use Filament\Facades\Filament;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $result = app(TrainingService::class)->enroll(
            Filament::getTenant()?->id,
            (int) $data['user_id'],
            (int) $data['course_id'],
        );

        return $result['enrollment'];
    }
}
