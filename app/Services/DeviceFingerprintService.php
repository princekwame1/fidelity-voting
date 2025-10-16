<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DeviceFingerprintService
{
    // Maximum devices allowed to vote from the same IP
    private const MAX_DEVICES_PER_IP = 5;

    // Time window for rate limiting (in minutes)
    private const RATE_LIMIT_WINDOW = 60;

    public function generateFingerprint(Request $request): string
    {
        $components = [
            'user_agent' => $request->userAgent() ?? '',
            'accept_language' => $request->header('Accept-Language') ?? '',
            'accept_encoding' => $request->header('Accept-Encoding') ?? '',
            'accept' => $request->header('Accept') ?? '',
            'dnt' => $request->header('DNT') ?? '',
            'connection' => $request->header('Connection') ?? '',
        ];

        // Add JavaScript-provided fingerprint data if available
        if ($request->has('fingerprint_data')) {
            $fpData = json_decode($request->input('fingerprint_data'), true);
            if ($fpData) {
                $components = array_merge($components, $fpData);
            }
        }

        // Sort to ensure consistent ordering
        ksort($components);
        $fingerprintString = json_encode($components);

        return hash('sha256', $fingerprintString);
    }

    public function generateStrictFingerprint(Request $request): string
    {
        // This includes IP address for stricter matching
        $components = [
            'user_agent' => $request->userAgent() ?? '',
            'accept_language' => $request->header('Accept-Language') ?? '',
            'accept_encoding' => $request->header('Accept-Encoding') ?? '',
            'ip_address' => $request->ip(),
            'ip_subnet' => $this->getIpSubnet($request->ip()),
        ];

        if ($request->has('fingerprint_data')) {
            $fpData = json_decode($request->input('fingerprint_data'), true);
            if ($fpData) {
                $components = array_merge($components, $fpData);
            }
        }

        ksort($components);
        return hash('sha256', json_encode($components));
    }

    private function getIpSubnet(string $ip): string
    {
        // Get the /24 subnet for IPv4 or /64 for IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.0/24';
        } else {
            // Simplified IPv6 subnet detection
            return $ip;
        }
    }

    public function validateFingerprint(string $storedFingerprint, Request $request): bool
    {
        $currentFingerprint = $this->generateFingerprint($request);
        return hash_equals($storedFingerprint, $currentFingerprint);
    }

    public function checkRateLimit(string $eventId, string $ip): bool
    {
        $key = "voting_rate_limit:{$eventId}:{$ip}";
        $attempts = Cache::get($key, 0);

        if ($attempts >= self::MAX_DEVICES_PER_IP) {
            return false;
        }

        Cache::put($key, $attempts + 1, now()->addMinutes(self::RATE_LIMIT_WINDOW));
        return true;
    }

    public function recordDeviceVote(string $eventId, string $deviceHash, string $ip): void
    {
        // Record this device has voted for this event
        $deviceKey = "device_voted:{$eventId}:{$deviceHash}";
        Cache::put($deviceKey, true, now()->addDays(7));

        // Track IP usage
        $ipKey = "ip_votes:{$eventId}:{$ip}";
        $ipVotes = Cache::get($ipKey, []);
        $ipVotes[] = [
            'device_hash' => $deviceHash,
            'timestamp' => now()->toIso8601String()
        ];
        Cache::put($ipKey, $ipVotes, now()->addDays(7));
    }

    public function hasDeviceVoted(string $eventId, string $deviceHash): bool
    {
        $key = "device_voted:{$eventId}:{$deviceHash}";
        return Cache::has($key);
    }

    public function getIpVoteCount(string $eventId, string $ip): int
    {
        $key = "ip_votes:{$eventId}:{$ip}";
        $votes = Cache::get($key, []);
        return count($votes);
    }

    public function isSuspiciousActivity(string $eventId, Request $request): array
    {
        $ip = $request->ip();
        $deviceHash = $this->generateFingerprint($request);

        $suspicionReasons = [];

        // Check if too many votes from same IP
        $ipVotes = $this->getIpVoteCount($eventId, $ip);
        if ($ipVotes >= self::MAX_DEVICES_PER_IP) {
            $suspicionReasons[] = "Too many votes from IP address ({$ipVotes} devices)";
        }

        // Check if device already voted
        if ($this->hasDeviceVoted($eventId, $deviceHash)) {
            $suspicionReasons[] = "Device has already voted";
        }

        // Check for VPN/Proxy indicators
        if ($this->isUsingVpnOrProxy($request)) {
            $suspicionReasons[] = "Possible VPN/Proxy detected";
        }

        // Check for bot patterns
        if ($this->hasBotPatterns($request)) {
            $suspicionReasons[] = "Bot-like behavior detected";
        }

        return [
            'is_suspicious' => !empty($suspicionReasons),
            'reasons' => $suspicionReasons,
            'risk_score' => count($suspicionReasons) * 25 // 0-100 scale
        ];
    }

    private function isUsingVpnOrProxy(Request $request): bool
    {
        // Check common VPN/Proxy headers
        $proxyHeaders = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_VIA',
        ];

        foreach ($proxyHeaders as $header) {
            if ($request->server($header)) {
                return true;
            }
        }

        return false;
    }

    private function hasBotPatterns(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        // Check for missing or suspicious user agents
        if (empty($userAgent)) {
            return true;
        }

        // Check for common bot patterns
        $botPatterns = ['bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python'];
        foreach ($botPatterns as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                return true;
            }
        }

        // Check if JavaScript fingerprint data is missing (likely a bot)
        if (!$request->has('fingerprint_data')) {
            return true;
        }

        return false;
    }

    public function getJavaScriptFingerprintCode(): string
    {
        return <<<'JS'
        async function generateDeviceFingerprint() {
            const fp = {};

            // Screen properties
            fp.screen_width = screen.width;
            fp.screen_height = screen.height;
            fp.screen_depth = screen.colorDepth;
            fp.screen_pixel_ratio = window.devicePixelRatio || 1;

            // Browser properties
            fp.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            fp.language = navigator.language;
            fp.languages = navigator.languages.join(',');
            fp.platform = navigator.platform;
            fp.hardware_concurrency = navigator.hardwareConcurrency || 0;
            fp.device_memory = navigator.deviceMemory || 0;

            // Canvas fingerprint
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                ctx.textBaseline = 'top';
                ctx.font = '14px "Arial"';
                ctx.textBaseline = 'alphabetic';
                ctx.fillStyle = '#f60';
                ctx.fillRect(125, 1, 62, 20);
                ctx.fillStyle = '#069';
                ctx.fillText('Device fingerprint £€', 2, 15);
                ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
                ctx.fillText('Device fingerprint £€', 4, 17);
                fp.canvas_hash = canvas.toDataURL().slice(-100);
            } catch (e) {
                fp.canvas_hash = 'unavailable';
            }

            // WebGL fingerprint
            try {
                const canvas = document.createElement('canvas');
                const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                if (gl) {
                    const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                    if (debugInfo) {
                        fp.webgl_vendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
                        fp.webgl_renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
                    }
                }
            } catch (e) {
                fp.webgl_vendor = 'unavailable';
            }

            // Audio fingerprint
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const analyser = audioContext.createAnalyser();
                const gain = audioContext.createGain();
                const scriptProcessor = audioContext.createScriptProcessor(4096, 1, 1);

                gain.gain.value = 0; // Mute
                oscillator.connect(analyser);
                analyser.connect(scriptProcessor);
                scriptProcessor.connect(gain);
                gain.connect(audioContext.destination);

                oscillator.start(0);
                let audioData = '';
                scriptProcessor.onaudioprocess = function(event) {
                    const output = event.inputBuffer.getChannelData(0);
                    audioData = output.slice(0, 10).toString();
                };

                setTimeout(() => {
                    fp.audio_fingerprint = audioData.slice(0, 30);
                    oscillator.stop();
                    audioContext.close();
                }, 100);
            } catch (e) {
                fp.audio_fingerprint = 'unavailable';
            }

            // Touch support
            fp.touch_support = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

            // Plugins (for older browsers)
            if (navigator.plugins) {
                const plugins = [];
                for (let i = 0; i < navigator.plugins.length && i < 10; i++) {
                    plugins.push(navigator.plugins[i].name);
                }
                fp.plugins = plugins.join(',');
            }

            return JSON.stringify(fp);
        }

        // Auto-submit fingerprint with form
        document.addEventListener('DOMContentLoaded', async function() {
            const fingerprintData = await generateDeviceFingerprint();

            // Add to all forms
            document.querySelectorAll('form').forEach(form => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'fingerprint_data';
                input.value = fingerprintData;
                form.appendChild(input);
            });

            // Store in session storage for AJAX requests
            sessionStorage.setItem('device_fingerprint', fingerprintData);
        });
        JS;
    }
}