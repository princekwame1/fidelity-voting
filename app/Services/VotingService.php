<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Question;
use App\Models\Option;
use App\Models\VoteToken;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VotingService
{
    public function __construct(
        private DeviceFingerprintService $fingerprintService
    ) {}

    public function validateTokenAccess(string $signedToken, int $eventId, Request $request): array
    {
        $qrService = new QRCodeService();
        $token = $qrService->verifySignedToken($signedToken);

        if (!$token) {
            return ['success' => false, 'error' => 'Invalid token signature'];
        }

        $voteToken = VoteToken::where('token', $token)
            ->where('event_id', $eventId)
            ->first();

        if (!$voteToken) {
            return ['success' => false, 'error' => 'Token not found'];
        }

        $event = $voteToken->event;

        if (!$event->isActive()) {
            if (!$event->hasStarted()) {
                return ['success' => false, 'error' => 'Voting has not started yet'];
            }
            if ($event->hasEnded()) {
                return ['success' => false, 'error' => 'Voting has ended'];
            }
        }

        if ($voteToken->isUsed()) {
            return ['success' => false, 'error' => 'This voting code has already been used'];
        }

        $deviceHash = $this->fingerprintService->generateFingerprint($request);
        $ipAddress = $request->ip();

        // If token hasn't been opened yet, mark it as opened
        if (!$voteToken->first_opened_at) {
            $voteToken->markAsOpened($deviceHash, $ipAddress);
        } else {
            // Check if it's the same device
            if (!$voteToken->isValidForDevice($deviceHash)) {
                return ['success' => false, 'error' => 'This voting code was opened on a different device'];
            }

            // Check if token has expired
            if ($voteToken->isExpired()) {
                return ['success' => false, 'error' => 'This voting code has expired'];
            }
        }

        return [
            'success' => true,
            'token' => $voteToken,
            'event' => $event,
            'device_hash' => $deviceHash,
            'ip_address' => $ipAddress
        ];
    }

    public function submitVote(VoteToken $voteToken, array $answers, string $deviceHash, string $ipAddress): array
    {
        return DB::transaction(function () use ($voteToken, $answers, $deviceHash, $ipAddress) {
            // Double-check token is still valid
            if (!$voteToken->canBeUsed($deviceHash)) {
                return ['success' => false, 'error' => 'Token is no longer valid'];
            }

            $event = $voteToken->event;
            $votesCreated = [];

            try {
                foreach ($answers as $questionId => $optionIds) {
                    $question = Question::where('id', $questionId)
                        ->where('event_id', $event->id)
                        ->first();

                    if (!$question) {
                        throw new \Exception("Invalid question ID: {$questionId}");
                    }

                    // Ensure we have an array for multiple choice handling
                    $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];

                    // For single choice questions, only allow one option
                    if (!$question->multiple_choice && count($optionIds) > 1) {
                        throw new \Exception("Question '{$question->question_text}' only allows one selection");
                    }

                    foreach ($optionIds as $optionId) {
                        $option = Option::where('id', $optionId)
                            ->where('question_id', $questionId)
                            ->first();

                        if (!$option) {
                            throw new \Exception("Invalid option ID: {$optionId} for question: {$questionId}");
                        }

                        // Check if vote already exists for this question+token (should not happen due to unique constraint)
                        $existingVote = Vote::where('question_id', $questionId)
                            ->where('vote_token_id', $voteToken->id)
                            ->first();

                        if ($existingVote) {
                            throw new \Exception("Vote already exists for question: {$questionId}");
                        }

                        $vote = Vote::castVote(
                            $event->id,
                            $questionId,
                            $optionId,
                            $voteToken,
                            $ipAddress,
                            $deviceHash
                        );

                        $votesCreated[] = $vote;
                    }
                }

                // Mark token as used
                $voteToken->markAsUsed();

                return [
                    'success' => true,
                    'message' => 'Your votes have been recorded successfully',
                    'votes_count' => count($votesCreated)
                ];

            } catch (\Exception $e) {
                throw $e; // This will trigger the transaction rollback
            }
        });
    }

    public function getEventResults(Event $event): array
    {
        $questions = $event->questions()
            ->with(['options.votes', 'votes'])
            ->get();

        $results = [];

        foreach ($questions as $question) {
            $totalVotes = $question->votes->count();
            $options = [];

            foreach ($question->options as $option) {
                $voteCount = $option->votes->count();
                $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0;

                $options[] = [
                    'id' => $option->id,
                    'text' => $option->option_text,
                    'votes' => $voteCount,
                    'percentage' => $percentage
                ];
            }

            $results[] = [
                'id' => $question->id,
                'text' => $question->question_text,
                'multiple_choice' => $question->multiple_choice,
                'total_votes' => $totalVotes,
                'options' => $options
            ];
        }

        return [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'total_votes' => $event->total_votes,
                'used_tokens' => $event->used_tokens_count,
                'total_tokens' => $event->total_tokens_count
            ],
            'questions' => $results
        ];
    }

    public function getEventStats(Event $event): array
    {
        $totalSessions = $event->votingSessions()->count();
        $votedSessions = $event->votingSessions()->where('has_voted', true)->count();
        $activeSessions = $event->votingSessions()
            ->where('expires_at', '>', now())
            ->where('has_voted', false)
            ->count();

        $totalVotes = $event->votes()->count();
        $uniqueVoters = $event->votes()->distinct('voting_session_id')->count();

        $votingPattern = $event->votes()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'sessions' => [
                'total' => $totalSessions,
                'voted' => $votedSessions,
                'active' => $activeSessions,
                'usage_rate' => $totalSessions > 0 ? round(($votedSessions / $totalSessions) * 100, 2) : 0
            ],
            'votes' => [
                'total' => $totalVotes,
                'unique_voters' => $uniqueVoters,
                'questions_count' => $event->questions()->count()
            ],
            'voting_pattern' => $votingPattern->toArray()
        ];
    }
}