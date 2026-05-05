<?php

namespace App\Console\Commands;

use App\Models\Quality\Improvement\Action;
use Illuminate\Console\Command;

class MarkOverdueImprovementActions extends Command
{
    protected $signature = 'improvement:mark-overdue-actions';

    protected $description = 'Mark quality improvement actions as overdue when due date is past and action is not completed.';

    public function handle(): int
    {
        $updated = Action::query()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereNull('completed_at')
            ->whereNotIn('status', ['terminada', 'vencida'])
            ->update(['status' => 'vencida']);

        $this->info("Overdue improvement actions updated: {$updated}");

        return self::SUCCESS;
    }
}

