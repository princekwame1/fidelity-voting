<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class Event extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'voting_duration_minutes',
        'show_results_table',
        'collect_emails',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'show_results_table' => 'boolean',
        'collect_emails' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function votingSessions(): HasMany
    {
        return $this->hasMany(VotingSession::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function isActive(): bool
    {
        $now = Carbon::now();
        return $now->between($this->start_time, $this->end_time);
    }

    public function hasStarted(): bool
    {
        return Carbon::now()->isAfter($this->start_time);
    }

    public function hasEnded(): bool
    {
        return Carbon::now()->isAfter($this->end_time);
    }

    public function getTotalVotesAttribute(): int
    {
        return $this->votes()->count();
    }

    public function getVotedSessionsCountAttribute(): int
    {
        return $this->votingSessions()->where('has_voted', true)->count();
    }

    public function getTotalSessionsCountAttribute(): int
    {
        return $this->votingSessions()->count();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the encrypted ID for URLs (5 characters)
     */
    public function getEncryptedIdAttribute(): string
    {
        // Create a simple hash-based short ID
        $hash = md5($this->id . config('app.key'));
        return substr($hash, 0, 5);
    }

    /**
     * Find event by short encrypted ID
     */
    public static function findByEncryptedId(string $encryptedId): ?self
    {
        // Find all events and check which one matches the hash
        $events = self::all();
        foreach ($events as $event) {
            $hash = md5($event->id . config('app.key'));
            if (substr($hash, 0, 5) === $encryptedId) {
                return $event;
            }
        }
        return null;
    }
}
