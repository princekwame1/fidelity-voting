<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VotingSession extends Model
{
    protected $fillable = [
        'event_id',
        'session_token',
        'device_hash',
        'ip_address',
        'has_voted',
        'expires_at',
    ];

    protected $casts = [
        'has_voted' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('session_token', $token)->exists());

        return $token;
    }

    public static function createForEvent(Event $event, string $deviceHash, string $ipAddress): self
    {
        // Use event's voting duration or default to 30 minutes
        $durationMinutes = $event->voting_duration_minutes ?? 30;

        return self::create([
            'event_id' => $event->id,
            'session_token' => self::generateUniqueToken(),
            'device_hash' => $deviceHash,
            'ip_address' => $ipAddress,
            'expires_at' => Carbon::now()->addMinutes($durationMinutes),
        ]);
    }

    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    public function canVote(): bool
    {
        return !$this->has_voted && !$this->isExpired() && $this->event->isActive();
    }

    public function markAsVoted(): void
    {
        $this->update(['has_voted' => true]);
    }

    public function scopeActive($query)
    {
        return $query->where('has_voted', false)
                    ->where('expires_at', '>', Carbon::now());
    }

    public function scopeForDevice($query, string $deviceHash)
    {
        return $query->where('device_hash', $deviceHash);
    }
}
