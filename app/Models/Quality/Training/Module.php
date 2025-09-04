<?php

namespace App\Models\Quality\Training;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    /** @use HasFactory<\Database\Factories\Quality\Training\ModuleFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'objective',
        'description',
        'duration', // Duration in minutes
        'course_id',
        'order',
        'image',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'duration' => 'integer',
    ];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function getIsActiveAttribute()
    {
        return $this->active;
    }
    public function getDurationInHoursAttribute()
    {
        return $this->duration / 60; // Convert minutes to hours
    }
    public function getDurationInHoursFormattedAttribute()
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    
}
