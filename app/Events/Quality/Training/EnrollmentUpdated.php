<?php

namespace App\Events\Quality\Training;

use App\Models\Quality\Training\Enrollment;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnrollmentUpdated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Enrollment $enrollment,
        public array $oldValues = [],
        public array $newValues = [],
    ) {}
}
