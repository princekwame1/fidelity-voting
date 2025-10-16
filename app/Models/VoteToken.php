<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VoteToken extends Model
{
    protected $fillable = [
        'event_id',
        'token',
        'device_hash',
        'ip_address',
        'used_at',
        'first_opened_at',
        'expires_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'first_opened_at' => 'datetime',
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
        } while (self::where('token', $token)->exists());

        return $token;
    }

    public function markAsOpened(string $deviceHash, string $ipAddress): bool
    {
        if ($this->first_opened_at) {
            return false;
        }

        $this->update([
            'device_hash' => $deviceHash,
            'ip_address' => $ipAddress,
            'first_opened_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        return true;
    }

    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && Carbon::now()->isAfter($this->expires_at);
    }

    public function isValidForDevice(string $deviceHash): bool
    {
        return $this->device_hash === $deviceHash;
    }

    public function canBeUsed(string $deviceHash): bool
    {
        return !$this->isUsed() &&
               !$this->isExpired() &&
               $this->isValidForDevice($deviceHash) &&
               $this->event->isActive();
    }

    public function markAsUsed(): void
    {
        $this->update(['used_at' => Carbon::now()]);
    }

    public function scopeAvailable($query)
    {
        return $query->whereNull('used_at')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', Carbon::now());
                    });
    }

    public function scopeUsed($query)
    {
        return $query->whereNotNull('used_at');
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<', Carbon::now());
    }
}
