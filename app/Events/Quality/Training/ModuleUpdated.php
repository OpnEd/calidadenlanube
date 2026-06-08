<?php

namespace App\Events\Quality\Training;

use App\Models\Quality\Training\Module;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModuleUpdated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Module $module,
        public array $oldValues = [],
        public array $newValues = [],
    ) {}
}
