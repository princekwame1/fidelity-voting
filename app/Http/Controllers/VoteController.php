<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DeviceFingerprintService;
use App\Models\Event;
use App\Models\VotingSession;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VoteController extends Controller
{
    public function __construct(
        private DeviceFingerprintService $fingerprintService
    ) {}

    public function show(Request $request, Event $event): View|JsonResponse
    {
        // Check if event is active
        if (!$event->isActive()) {
            if (!$event->hasStarted()) {
                $message = 'Voting has not started yet';
            } elseif ($event->hasEnded()) {
                $message = 'Voting has ended';
            } else {
                $message = 'Event is not active';
            }

            if ($request->expectsJson()) {
                return response()->json(['error' => $message], 400);
            }
            return view('vote.error', ['error' => $message]);
        }

        $strictDeviceHash = $this->fingerprintService->generateStrictFingerprint($request);
        $ipAddress = $request->ip();

        // Skip suspicious activity check to allow multiple users from same IP
        // We still prevent same device from voting twice below

        // Removed IP rate limiting to allow multiple users from same location
        // Device-based checking is sufficient

        // Check if this device already has a session for this event
        $existingSession = VotingSession::where('event_id', $event->id)
            ->where('device_hash', $strictDeviceHash)
            ->first();

        if ($existingSession) {
            if ($existingSession->has_voted) {
                // Record as suspicious attempt
                $this->fingerprintService->recordDeviceVote($event->id, $strictDeviceHash, $ipAddress);

                if ($request->expectsJson()) {
                    return response()->json(['error' => 'This device has already voted'], 400);
                }
                return view('vote.error', ['error' => 'This device has already voted for this event']);
            }

            // Check if session is expired
            if ($existingSession->isExpired()) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Your voting session has expired'], 400);
                }
                return view('vote.error', ['error' => 'Your voting session has expired. Please scan the QR code again.']);
            }

            $session = $existingSession;
        } else {
            // Check if this specific device has voted (using strict fingerprint)
            if ($this->fingerprintService->hasDeviceVoted($event->id, $strictDeviceHash)) {
                Log::warning('Attempted vote from already voted device', [
                    'event_id' => $event->id,
                    'device_hash' => $strictDeviceHash,
                    'ip' => $ipAddress
                ]);

                if ($request->expectsJson()) {
                    return response()->json(['error' => 'This device has already participated in voting'], 403);
                }
                return view('vote.error', ['error' => 'This device has already participated in voting']);
            }

            // Create new session with strict device binding
            $session = VotingSession::create([
                'event_id' => $event->id,
                'session_token' => VotingSession::generateUniqueToken(),
                'device_hash' => $strictDeviceHash,
                'ip_address' => $ipAddress,
                'expires_at' => Carbon::now()->addMinutes($event->voting_duration_minutes ?? 30),
            ]);
        }

        $questions = $event->questions()->with('options')->get();

        if ($request->expectsJson()) {
            return response()->json([
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time
                ],
                'questions' => $questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'text' => $question->question_text,
                        'multiple_choice' => $question->multiple_choice,
                        'options' => $question->options->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'text' => $option->option_text
                            ];
                        })
                    ];
                }),
                'session_token' => $session->session_token,
                'expires_at' => $session->expires_at,
                'fingerprint_code' => $this->fingerprintService->getJavaScriptFingerprintCode(),
                'csrf_token' => csrf_token()
            ]);
        }

        return view('vote.form', [
            'event' => $event,
            'questions' => $questions,
            'session_token' => $session->session_token,
            'expires_at' => $session->expires_at,
            'fingerprint_code' => $this->fingerprintService->getJavaScriptFingerprintCode()
        ]);
    }

    public function submit(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string',
            'answers' => 'required|array',
            'answers.*' => 'required',
            'fingerprint_data' => 'nullable|string'
        ]);

        // Verify session token
        $session = VotingSession::where('session_token', $validated['session_token'])
            ->where('event_id', $event->id)
            ->first();

        if (!$session) {
            return response()->json(['error' => 'Invalid session'], 403);
        }

        if ($session->has_voted) {
            return response()->json(['error' => 'Already voted'], 400);
        }

        if ($session->isExpired()) {
            return response()->json(['error' => 'Session expired'], 400);
        }

        // Verify device fingerprint matches
        $currentDeviceHash = $this->fingerprintService->generateStrictFingerprint($request);
        if ($session->device_hash !== $currentDeviceHash) {
            Log::warning('Device fingerprint mismatch during vote submission', [
                'event_id' => $event->id,
                'session_id' => $session->id,
                'expected_hash' => $session->device_hash,
                'current_hash' => $currentDeviceHash
            ]);
            return response()->json(['error' => 'Device verification failed. Please vote from the original device.'], 403);
        }

        // Removed secondary suspicious activity check
        // Device fingerprint validation is sufficient

        return DB::transaction(function () use ($validated, $event, $session, $request) {
            $votesCreated = [];

            foreach ($validated['answers'] as $questionId => $optionIds) {
                $question = $event->questions()->find($questionId);

                if (!$question) {
                    throw new \Exception("Invalid question ID: {$questionId}");
                }

                $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];

                if (!$question->multiple_choice && count($optionIds) > 1) {
                    throw new \Exception("Question only allows one selection");
                }

                foreach ($optionIds as $optionId) {
                    $option = $question->options()->find($optionId);

                    if (!$option) {
                        throw new \Exception("Invalid option ID: {$optionId}");
                    }

                    $vote = Vote::create([
                        'event_id' => $event->id,
                        'question_id' => $questionId,
                        'option_id' => $optionId,
                        'voting_session_id' => $session->id,
                        'ip_address' => $request->ip(),
                        'device_hash' => $session->device_hash
                    ]);

                    $votesCreated[] = $vote;
                }
            }

            // Mark session as voted
            $session->markAsVoted();

            // Record device vote for tracking
            $this->fingerprintService->recordDeviceVote(
                $event->id,
                $session->device_hash,
                $request->ip()
            );

            return response()->json([
                'success' => true,
                'message' => 'Your votes have been recorded successfully',
                'votes_count' => count($votesCreated),
                'redirect_url' => route('vote.success')
            ]);
        });
    }

    public function success(): View
    {
        return view('vote.success');
    }

    public function results(Event $event): View
    {
        $questions = $event->questions()->with(['options.votes'])->get();

        $results = $questions->map(function ($question) {
            $totalVotes = $question->options->sum(fn($option) => $option->votes->count());

            return [
                'question' => $question->question_text,
                'multiple_choice' => $question->multiple_choice,
                'total_votes' => $totalVotes,
                'options' => $question->options->map(function ($option) use ($totalVotes) {
                    $voteCount = $option->votes->count();
                    $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0;

                    return [
                        'text' => $option->option_text,
                        'votes' => $voteCount,
                        'percentage' => $percentage
                    ];
                })
            ];
        });

        // Get security statistics
        $securityStats = [
            'total_sessions' => $event->votingSessions()->count(),
            'voted_sessions' => $event->votingSessions()->where('has_voted', true)->count(),
            'unique_ips' => $event->votingSessions()->distinct('ip_address')->count(),
            'suspicious_attempts' => 0 // This would come from logs in production
        ];

        return view('vote.results-chart', [
            'event' => $event,
            'results' => $results,
            'security_stats' => $securityStats
        ]);
    }

    public function resultsData(Event $event): JsonResponse
    {
        $questions = $event->questions()->with(['options.votes'])->get();

        $results = $questions->map(function ($question) {
            $totalVotes = $question->options->sum(fn($option) => $option->votes->count());

            return [
                'id' => $question->id,
                'question' => $question->question_text,
                'multiple_choice' => $question->multiple_choice,
                'total_votes' => $totalVotes,
                'options' => $question->options->map(function ($option) use ($totalVotes) {
                    $voteCount = $option->votes->count();
                    $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0;

                    return [
                        'id' => $option->id,
                        'text' => $option->option_text,
                        'votes' => $voteCount,
                        'percentage' => $percentage
                    ];
                })
            ];
        });

        return response()->json([
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'total_votes' => $event->votes()->count(),
                'total_participants' => $event->votingSessions()->where('has_voted', true)->count()
            ],
            'results' => $results
        ]);
    }
}