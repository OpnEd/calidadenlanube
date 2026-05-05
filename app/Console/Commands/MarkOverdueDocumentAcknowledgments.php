<?php

namespace App\Console\Commands;

use App\Models\Quality\Documentation\DocumentAcknowledgment;
use Illuminate\Console\Command;

class MarkOverdueDocumentAcknowledgments extends Command
{
    protected $signature = 'documents:mark-overdue-acks';

    protected $description = 'Mark pending required document acknowledgments as overdue.';

    public function handle(): int
    {
        $updated = DocumentAcknowledgment::query()
            ->where('required', true)
            ->where('status', 'pending')
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', now()->toDateString())
            ->whereNull('acknowledged_at')
            ->update(['status' => 'overdue']);

        $this->info("Overdue document acknowledgments updated: {$updated} ");

        return self::SUCCESS;
    }
}

