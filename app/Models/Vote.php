<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = [
        'event_id',
        'question_id',
        'option_id',
        'voting_session_id',
        'ip_address',
        'device_hash',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    public function votingSession(): BelongsTo
    {
        return $this->belongsTo(VotingSession::class);
    }

    public static function castVote(
        int $eventId,
        int $questionId,
        int $optionId,
        VotingSession $session,
        string $ipAddress,
        string $deviceHash
    ): self {
        return self::create([
            'event_id' => $eventId,
            'question_id' => $questionId,
            'option_id' => $optionId,
            'voting_session_id' => $session->id,
            'ip_address' => $ipAddress,
            'device_hash' => $deviceHash,
        ]);
    }

    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeForQuestion($query, int $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    public function scopeForOption($query, int $optionId)
    {
        return $query->where('option_id', $optionId);
    }
}
