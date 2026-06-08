<?php

use App\Models\Quality\Training\Lesson;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('progress') && ! Schema::hasTable('enrollment_lesson')) {
            Schema::rename('progress', 'enrollment_lesson');
        }

        if (! Schema::hasTable('enrollment_lesson')) {
            Schema::create('enrollment_lesson', function (Blueprint $table) {
                $table->id();
                $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->cascadeOnDelete();
                $table->foreignId('lesson_id')->nullable()->constrained('lessons')->cascadeOnDelete();
                $table->string('status', 20)->default('not_started');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('last_accessed_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('enrollment_lesson', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollment_lesson', 'enrollment_id')) {
                $table->foreignId('enrollment_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('enrollments')
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('enrollment_lesson', 'lesson_id')) {
                $table->foreignId('lesson_id')
                    ->nullable()
                    ->after('enrollment_id')
                    ->constrained('lessons')
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('enrollment_lesson', 'status')) {
                $table->string('status', 20)->default('not_started')->after('lesson_id');
            }

            if (! Schema::hasColumn('enrollment_lesson', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('enrollment_lesson', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }

            if (! Schema::hasColumn('enrollment_lesson', 'last_accessed_at')) {
                $table->timestamp('last_accessed_at')->nullable()->after('completed_at');
            }

            if (! Schema::hasColumn('enrollment_lesson', 'consumed_at')) {
                $table->timestamp('consumed_at')->nullable()->after('last_accessed_at');
            }

            if (! Schema::hasColumn('enrollment_lesson', 'passed')) {
                $table->boolean('passed')->default(false)->after('consumed_at');
            }

            if (! Schema::hasColumn('enrollment_lesson', 'passed_at')) {
                $table->timestamp('passed_at')->nullable()->after('passed');
            }

            if (! Schema::hasColumn('enrollment_lesson', 'approved_attempt_id')) {
                $table->unsignedBigInteger('approved_attempt_id')->nullable()->after('passed_at');
            }

            if (! Schema::hasColumn('enrollment_lesson', 'certificate_issued_at')) {
                $table->timestamp('certificate_issued_at')->nullable()->after('approved_attempt_id');
            }

            if (! Schema::hasColumn('enrollment_lesson', 'certificate_url')) {
                $table->string('certificate_url')->nullable()->after('certificate_issued_at');
            }

            if (! Schema::hasColumn('enrollment_lesson', 'certificate_code')) {
                $table->string('certificate_code')->nullable()->after('certificate_url');
            }
        });

        Schema::table('enrollment_lesson', function (Blueprint $table) {
            $table->string('status', 20)->default('not_started')->change();
        });

        if (! Schema::hasIndex('enrollment_lesson', ['enrollment_id', 'lesson_id'], 'unique')) {
            Schema::table('enrollment_lesson', function (Blueprint $table) {
                $table->unique(['enrollment_id', 'lesson_id'], 'enrollment_lesson_enrollment_lesson_unique');
            });
        }

        if (! $this->hasForeign('enrollment_lesson', ['approved_attempt_id'])) {
            Schema::table('enrollment_lesson', function (Blueprint $table) {
                $table->foreign('approved_attempt_id', 'enrollment_lesson_approved_attempt_foreign')
                    ->references('id')
                    ->on('assessment_attempts')
                    ->nullOnDelete();
            });
        }

        DB::table('enrollment_lesson')
            ->whereNull('consumed_at')
            ->whereNotNull('completed_at')
            ->update(['consumed_at' => DB::raw('completed_at')]);

        $this->backfillEnrollmentLessons();
    }

    public function down(): void
    {
        if (! Schema::hasTable('enrollment_lesson')) {
            return;
        }

        if ($this->hasForeign('enrollment_lesson', ['approved_attempt_id'])) {
            Schema::table('enrollment_lesson', function (Blueprint $table) {
                $table->dropForeign('enrollment_lesson_approved_attempt_foreign');
            });
        }

        if (Schema::hasIndex('enrollment_lesson', ['enrollment_id', 'lesson_id'], 'unique')) {
            Schema::table('enrollment_lesson', function (Blueprint $table) {
                $table->dropUnique('enrollment_lesson_enrollment_lesson_unique');
            });
        }

        Schema::table('enrollment_lesson', function (Blueprint $table) {
            foreach ([
                'certificate_code',
                'certificate_url',
                'certificate_issued_at',
                'approved_attempt_id',
                'passed_at',
                'passed',
                'consumed_at',
            ] as $column) {
                if (Schema::hasColumn('enrollment_lesson', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function hasForeign(string $table, array $columns): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (($foreignKey['columns'] ?? null) === $columns) {
                return true;
            }
        }

        return false;
    }

    private function backfillEnrollmentLessons(): void
    {
        $courseLessons = DB::table('modules')
            ->join('lessons', 'lessons.module_id', '=', 'modules.id')
            ->select('modules.course_id', 'lessons.id as lesson_id')
            ->orderBy('lessons.id')
            ->get()
            ->groupBy('course_id');

        $now = now();

        DB::table('enrollments')
            ->select('id', 'course_id')
            ->orderBy('id')
            ->chunkById(100, function ($enrollments) use ($courseLessons, $now) {
                foreach ($enrollments as $enrollment) {
                    $lessons = $courseLessons->get($enrollment->course_id, collect());

                    foreach ($lessons as $lesson) {
                        $exists = DB::table('enrollment_lesson')
                            ->where('enrollment_id', $enrollment->id)
                            ->where('lesson_id', $lesson->lesson_id)
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        DB::table('enrollment_lesson')->insert([
                            'enrollment_id' => $enrollment->id,
                            'lesson_id' => $lesson->lesson_id,
                            'status' => 'not_started',
                            'passed' => false,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            });
    }
};
