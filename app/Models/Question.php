<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'event_id',
        'question_text',
        'multiple_choice',
    ];

    protected $casts = [
        'multiple_choice' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function getTotalVotesAttribute(): int
    {
        return $this->votes()->count();
    }

    public function getResultsAttribute(): array
    {
        return $this->options->map(function ($option) {
            return [
                'option_id' => $option->id,
                'option_text' => $option->option_text,
                'vote_count' => $option->votes()->count(),
                'percentage' => $this->total_votes > 0
                    ? round(($option->votes()->count() / $this->total_votes) * 100, 2)
                    : 0
            ];
        })->toArray();
    }
}
