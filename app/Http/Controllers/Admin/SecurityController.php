<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\VotingSession;
use App\Services\DeviceFingerprintService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecurityController extends Controller
{
    public function __construct(
        private DeviceFingerprintService $fingerprintService
    ) {}

    public function dashboard(): View
    {
        $events = Event::with('votingSessions')
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $securityStats = $this->getSecurityStatistics($events);

        return view('admin.security.dashboard', compact('events', 'securityStats'));
    }

    public function eventSecurity(Event $event): View
    {
        $sessions = $event->votingSessions()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $securityDetails = $this->getEventSecurityDetails($event);

        return view('admin.security.event', compact('event', 'sessions', 'securityDetails'));
    }

    public function suspiciousActivity(): View
    {
        // Get suspicious activity from logs (in production, you'd query log files or a dedicated table)
        $activities = $this->getSuspiciousActivities();

        return view('admin.security.suspicious', compact('activities'));
    }

    public function blockIp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ip' => 'required|ip',
            'reason' => 'required|string|max:255',
            'duration_hours' => 'required|integer|min:1|max:168' // Max 1 week
        ]);

        $key = "blocked_ip:{$validated['ip']}";
        Cache::put($key, [
            'reason' => $validated['reason'],
            'blocked_at' => now()->toIso8601String(),
            'blocked_by' => auth()->user()->name
        ], now()->addHours($validated['duration_hours']));

        Log::info('IP address blocked by admin', [
            'ip' => $validated['ip'],
            'reason' => $validated['reason'],
            'duration_hours' => $validated['duration_hours'],
            'admin_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "IP {$validated['ip']} has been blocked for {$validated['duration_hours']} hours"
        ]);
    }

    public function unblockIp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ip' => 'required|ip'
        ]);

        $key = "blocked_ip:{$validated['ip']}";
        Cache::forget($key);

        Log::info('IP address unblocked by admin', [
            'ip' => $validated['ip'],
            'admin_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "IP {$validated['ip']} has been unblocked"
        ]);
    }

    public function revokeSession(VotingSession $session): JsonResponse
    {
        if ($session->has_voted) {
            return response()->json(['error' => 'Cannot revoke session that has already voted'], 400);
        }

        // Mark session as expired
        $session->update(['expires_at' => now()]);

        Log::info('Voting session revoked by admin', [
            'session_id' => $session->id,
            'event_id' => $session->event_id,
            'admin_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session has been revoked'
        ]);
    }

    private function getSecurityStatistics($events): array
    {
        $totalSessions = $events->sum(fn($event) => $event->votingSessions->count());
        $votedSessions = $events->sum(fn($event) => $event->votingSessions->where('has_voted', true)->count());

        // Calculate IP distribution
        $ipCounts = [];
        foreach ($events as $event) {
            foreach ($event->votingSessions as $session) {
                $ipCounts[$session->ip_address] = ($ipCounts[$session->ip_address] ?? 0) + 1;
            }
        }

        // Find suspicious IPs (more than 5 votes from same IP across all events)
        $suspiciousIps = array_filter($ipCounts, fn($count) => $count > 5);

        // Get recent blocked IPs
        $blockedIps = $this->getBlockedIps();

        // Calculate device reuse rate
        $deviceHashes = $events->flatMap(fn($event) => $event->votingSessions->pluck('device_hash'))->toArray();
        $uniqueDevices = count(array_unique($deviceHashes));
        $deviceReuseRate = $totalSessions > 0 ? round((($totalSessions - $uniqueDevices) / $totalSessions) * 100, 2) : 0;

        return [
            'total_sessions' => $totalSessions,
            'voted_sessions' => $votedSessions,
            'unique_devices' => $uniqueDevices,
            'device_reuse_rate' => $deviceReuseRate,
            'suspicious_ips' => count($suspiciousIps),
            'blocked_ips' => count($blockedIps),
            'top_suspicious_ips' => array_slice($suspiciousIps, 0, 10, true),
            'blocked_ips_list' => $blockedIps
        ];
    }

    private function getEventSecurityDetails(Event $event): array
    {
        $sessions = $event->votingSessions;

        // IP analysis
        $ipCounts = $sessions->countBy('ip_address');
        $suspiciousIps = $ipCounts->filter(fn($count) => $count > 3);

        // Device analysis
        $deviceHashes = $sessions->pluck('device_hash');
        $duplicateDevices = $deviceHashes->duplicates();

        // Time analysis
        $votingPattern = $sessions->where('has_voted', true)
            ->groupBy(fn($session) => $session->created_at->format('Y-m-d H'))
            ->map(fn($sessions) => $sessions->count());

        // Detect rapid voting (more than 10 votes in one hour)
        $rapidVoting = $votingPattern->filter(fn($count) => $count > 10);

        return [
            'total_sessions' => $sessions->count(),
            'voted_sessions' => $sessions->where('has_voted', true)->count(),
            'unique_ips' => $sessions->unique('ip_address')->count(),
            'unique_devices' => $sessions->unique('device_hash')->count(),
            'suspicious_ips' => $suspiciousIps->count(),
            'duplicate_devices' => $duplicateDevices->count(),
            'rapid_voting_hours' => $rapidVoting->count(),
            'voting_pattern' => $votingPattern->take(24), // Last 24 hours
            'top_ips' => $ipCounts->sortDesc()->take(10),
            'expired_sessions' => $sessions->filter(fn($session) => $session->isExpired() && !$session->has_voted)->count()
        ];
    }

    private function getSuspiciousActivities(): array
    {
        // In production, you'd query from a dedicated suspicious_activities table or log files
        // For now, we'll return some mock data structure
        return [
            [
                'type' => 'multiple_devices_same_ip',
                'event_id' => 1,
                'ip_address' => '192.168.1.1',
                'device_count' => 8,
                'detected_at' => now()->subHours(2),
                'severity' => 'high'
            ],
            [
                'type' => 'bot_pattern_detected',
                'event_id' => 2,
                'ip_address' => '10.0.0.1',
                'user_agent' => 'curl/7.68.0',
                'detected_at' => now()->subHours(4),
                'severity' => 'medium'
            ],
            [
                'type' => 'rapid_voting',
                'event_id' => 1,
                'ip_address' => '203.0.113.1',
                'votes_per_minute' => 15,
                'detected_at' => now()->subMinutes(30),
                'severity' => 'high'
            ]
        ];
    }

    private function getBlockedIps(): array
    {
        // In production, you might want to store blocked IPs in database
        // For now, we'll try to get them from cache patterns
        $blockedIps = [];

        // This is a simplified approach - in production you'd have a better way to track this
        for ($i = 0; $i < 256; $i++) {
            for ($j = 0; $j < 256; $j++) {
                $testIp = "192.168.{$i}.{$j}";
                $key = "blocked_ip:{$testIp}";
                if (Cache::has($key)) {
                    $blockedIps[$testIp] = Cache::get($key);
                }
                // Limit check to prevent performance issues
                if (count($blockedIps) > 100) break 2;
            }
        }

        return $blockedIps;
    }

    public function getSecurityData(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '24h');

        $startTime = match($timeframe) {
            '1h' => now()->subHour(),
            '6h' => now()->subHours(6),
            '24h' => now()->subDay(),
            '7d' => now()->subWeek(),
            '30d' => now()->subMonth(),
            default => now()->subDay()
        };

        $sessions = VotingSession::where('created_at', '>=', $startTime)->get();

        $data = [
            'timeline' => $sessions->groupBy(fn($session) => $session->created_at->format('Y-m-d H:i'))
                ->map(fn($sessions) => $sessions->count())
                ->take(100),
            'ip_distribution' => $sessions->countBy('ip_address')->sortDesc()->take(20),
            'voting_success_rate' => $sessions->count() > 0
                ? round(($sessions->where('has_voted', true)->count() / $sessions->count()) * 100, 2)
                : 0,
            'suspicious_count' => $this->getSuspiciousActivitiesCount($sessions)
        ];

        return response()->json($data);
    }

    private function getSuspiciousActivitiesCount($sessions): int
    {
        $suspiciousCount = 0;

        // Count IPs with more than 5 sessions
        $ipCounts = $sessions->countBy('ip_address');
        $suspiciousCount += $ipCounts->filter(fn($count) => $count > 5)->count();

        // Add other suspicious patterns...

        return $suspiciousCount;
    }
}