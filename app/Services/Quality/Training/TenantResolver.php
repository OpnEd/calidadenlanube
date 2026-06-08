<?php

namespace App\Services\Quality\Training;

use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Lesson;
use App\Models\Quality\Training\Module;

class TenantResolver
{
    public static function resolveTeamId($model): ?int
    {
        if ($model instanceof Course) {
            return $model->team_id;
        }

        if ($model instanceof Module) {
            return $model->course?->team_id;
        }

        if ($model instanceof Lesson) {
            return $model->module?->course?->team_id;
        }

        if (isset($model->team_id)) {
            return $model->team_id;
        }

        return null;
    }
}
