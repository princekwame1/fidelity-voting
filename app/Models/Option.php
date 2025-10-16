<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Option extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'subtext',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function getVoteCountAttribute(): int
    {
        return $this->votes()->count();
    }

    public function getVotePercentageAttribute(): float
    {
        $totalVotes = $this->question->total_votes;
        return $totalVotes > 0 ? round(($this->vote_count / $totalVotes) * 100, 2) : 0;
    }
}
