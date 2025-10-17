<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoterEmail extends Model
{
    protected $fillable = [
        'event_id',
        'voting_session_id',
        'email',
        'device_hash',
        'ip_address',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function votingSession(): BelongsTo
    {
        return $this->belongsTo(VotingSession::class);
    }
}
