<?php

namespace App\Models\Quality\Training;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\Quality\Training\CourseFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'title',
        'objective',
        'description',
        'duration',
        'type',
        'level',
        'category',
        'instructor_id',
        'price',
        'image',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'float',
        'duration' => 'integer',
    ];

    public function getImageUrlAttribute()
    {
        $imagePath = storage_path('app/public/course_images/' . $this->image);

        if (!$this->image || !file_exists($imagePath)) {
            return asset('storage/course_images/default_course.png');
        }

        return asset('storage/course_images/' . $this->image);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function lessons()
    {
        // Relación hasManyThrough: Un curso tiene muchas lecciones a través de los módulos.
        // Esto permite acceder a todas las lecciones de un curso sin recorrer manualmente los módulos.
        return $this->hasManyThrough(Lesson::class, Module::class)->orderBy('order');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function getStatusAttribute()
    {
        return $this->pivot->status ?? null;
    }
    public function getProgressAttribute()
    {
        return $this->pivot->progress ?? 0;
    }
    public function getIsActiveAttribute()
    {
        return $this->active;
    }
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', '%' . $searchTerm . '%')
              ->orWhere('description', 'like', '%' . $searchTerm . '%')
              ->orWhere('objetctive', 'like', '%' . $searchTerm . '%');
        });
    }
    public function scopeWithInstructor($query)
    {
        return $query->with('instructor');
    }
    public function scopeWithUsers($query)
    {
        return $query->with('users');
    }

    public function scopeWithActiveStatus($query)
    {
        return $query->select('*', DB::raw("IF(active, 'Active', 'Inactive') as active_status"));
    }
    public function scopeWithProgress($query)
    {
        return $query->with(['users' => function ($q) {
            $q->select('id', 'name', 'email', 'pivot.progress');
        }]);
    }
    public function scopeWithStatus($query)
    {
        return $query->with(['users' => function ($q) {
            $q->select('id', 'name', 'email', 'pivot.status');
        }]);
    }
    public function scopeWithInstructorAndUsers($query)
    {
        return $query->with(['instructor', 'users']);
    }
    public function scopeWithAllDetails($query)
    {
        return $query->with(['instructor', 'users'])
                     ->withActiveStatus()
                     ->withProgress()
                     ->withStatus();
    }
    public function scopeWithAllDetailsAndInstructor($query, $instructorId)
    {
        return $query->with(['instructor' => function ($q) use ($instructorId) {
            $q->where('id', $instructorId);
        }])->with(['users' => function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        }])
          ->withActiveStatus()
          ->withProgress()
          ->withStatus();
    }

    // El creador (si es null, es Global)
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function isGlobal(): bool
    {
        return $this->team_id === null;
    }

    public function isOwnedByTeam(?int $teamId): bool
    {
        return $teamId !== null && (int) $this->team_id === $teamId;
    }

    /* public function isSharedWithTeam(?int $teamId): bool
    {
        if (! $this->isGlobal() || $teamId === null) {
            return false;
        }

        if ($this->relationLoaded('teams')) {
            return $this->teams->contains('id', $teamId);
        }

        return $this->teams()->whereKey($teamId)->exists();
    } */

    public function isVisibleToTeam(?int $teamId): bool
    {
        //return $this->isOwnedByTeam($teamId) || $this->isSharedWithTeam($teamId);
        return $this->isOwnedByTeam($teamId);
    }

    public function scopeOwnedByTeam(Builder $query, ?int $teamId): Builder
    {
        if ($teamId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('team_id', $teamId);
    }

    public function scopeDiscoverableToTeam(Builder $query, ?int $teamId): Builder
    {
        if ($teamId === null) {
            return $query->whereNull('team_id');
        }

        return $query->where(function (Builder $builder) use ($teamId) {
            $builder->whereNull('team_id')
                ->orWhere('team_id', $teamId);
        });
    }

    /* public function scopeVisibleToTeam(Builder $query, ?int $teamId): Builder
    {
        if ($teamId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $builder) use ($teamId) {
            $builder->where('team_id', $teamId)
                ->orWhere(function (Builder $sharedQuery) use ($teamId) {
                    $sharedQuery->whereNull('team_id')
                        ->whereHas('teams', fn (Builder $teamQuery) => $teamQuery->whereKey($teamId));
                });
        });
    } */
}
