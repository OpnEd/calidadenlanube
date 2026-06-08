<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assessment_attempts')) {
            return;
        }

        Schema::table('assessment_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('assessment_attempts', 'enrollment_id')) {
                $table->foreignId('enrollment_id')
                    ->nullable()
                    ->after('assessment_id')
                    ->constrained('enrollments')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('assessment_attempts', 'lesson_id')) {
                $table->foreignId('lesson_id')
                    ->nullable()
                    ->after('enrollment_id')
                    ->constrained('lessons')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('assessment_attempts', 'passed_at')) {
                $table->timestamp('passed_at')->nullable()->after('passed');
            }
        });

        DB::table('assessment_attempts')
            ->join('assessments', 'assessments.id', '=', 'assessment_attempts.assessment_id')
            ->whereNull('assessment_attempts.lesson_id')
            ->whereNotNull('assessments.lesson_id')
            ->update(['assessment_attempts.lesson_id' => DB::raw('assessments.lesson_id')]);

        $attempts = DB::table('assessment_attempts')
            ->join('assessments', 'assessments.id', '=', 'assessment_attempts.assessment_id')
            ->select(
                'assessment_attempts.id',
                'assessment_attempts.user_id',
                'assessments.course_id',
                'assessment_attempts.completed_at',
                'assessment_attempts.created_at'
            )
            ->whereNull('assessment_attempts.enrollment_id')
            ->orderBy('assessment_attempts.id')
            ->get();

        foreach ($attempts as $attempt) {
            $enrollmentId = DB::table('enrollments')
                ->where('user_id', $attempt->user_id)
                ->where('course_id', $attempt->course_id)
                ->orderByRaw('CASE WHEN completed_at IS NULL THEN 0 ELSE 1 END')
                ->orderBy('id')
                ->value('id');

            if ($enrollmentId) {
                DB::table('assessment_attempts')
                    ->where('id', $attempt->id)
                    ->update(['enrollment_id' => $enrollmentId]);
            }
        }

        DB::table('assessment_attempts')
            ->where('passed', true)
            ->whereNull('passed_at')
            ->update(['passed_at' => DB::raw('COALESCE(completed_at, created_at, CURRENT_TIMESTAMP)')]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('assessment_attempts')) {
            return;
        }

        Schema::table('assessment_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_attempts', 'passed_at')) {
                $table->dropColumn('passed_at');
            }

            if (Schema::hasColumn('assessment_attempts', 'lesson_id')) {
                $table->dropConstrainedForeignId('lesson_id');
            }

            if (Schema::hasColumn('assessment_attempts', 'enrollment_id')) {
                $table->dropConstrainedForeignId('enrollment_id');
            }
        });
    }
};
