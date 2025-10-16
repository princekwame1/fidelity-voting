<x-layouts.app title="QR Code - {{ $event->name }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-muted">QR Code for {{ $event->name }}</h4>
        <div>
            <a href="{{ route('admin.events.show', $event) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Event
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- QR Code Display -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Event QR Code</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <div id="qr-code-container" class="d-inline-block p-3 border rounded">
                            <!-- QR Code will be generated here -->
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Single QR Code for All Attendees</strong><br>
                        <small>All attendees scan the same QR code. Each device gets a unique session automatically.</small>
                    </div>

                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="vote-url" value="{{ $qrCodeUrl }}" readonly>
                        <button class="btn btn-secondary" type="button" onclick="copyToClipboard()">
                            <i class="fas fa-copy"></i> Copy URL
                        </button>
                    </div>
                </div>
            </div>

            <!-- Voting Sessions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Voting Sessions ({{ $sessions->total() }})</h5>
                </div>
                <div class="card-body">
                    @if($sessions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Session ID</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Expires</th>
                                        <th>Device Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sessions as $session)
                                        <tr>
                                            <td>
                                                <code>{{ substr($session->session_token, 0, 8) }}...</code>
                                            </td>
                                            <td>
                                                @if($session->has_voted)
                                                    <span class="badge bg-success">Voted</span>
                                                @elseif($session->expires_at > now())
                                                    <span class="badge bg-warning">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Expired</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $session->created_at->format('M d, g:i A') }}
                                            </td>
                                            <td>
                                                {{ $session->expires_at->format('M d, g:i A') }}
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ substr($session->device_hash, 0, 8) }}...<br>
                                                    {{ $session->ip_address }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $sessions->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No Voting Sessions Yet</h5>
                            <p class="text-muted">Sessions will appear here when attendees scan the QR code.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Download Options -->
            {{-- <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Download QR Code</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.events.qrcodes.download-png', $event) }}"
                           class="btn btn-primary">
                            <i class="fas fa-download"></i> Download PNG
                        </a>
                        <a href="{{ route('admin.events.qrcodes.download', $event) }}"
                           class="btn btn-primary">
                            <i class="fas fa-download"></i> Download SVG
                        </a>
                        <a href="{{ route('admin.events.qrcodes.download-sheet', $event) }}"
                           class="btn btn-secondary">
                            <i class="fas fa-file-download"></i> Download Printable Sheet
                        </a>
                    </div>
                </div>
            </div> --}}

            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Session Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Total Sessions:</strong> {{ $sessions->total() }}
                    </div>
                    <div class="mb-3">
                        <strong>Voted:</strong>
                        {{ $event->votingSessions()->where('has_voted', true)->count() }}
                        @if($sessions->total() > 0)
                            ({{ round(($event->votingSessions()->where('has_voted', true)->count() / $sessions->total()) * 100, 1) }}%)
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Active:</strong>
                        {{ $event->votingSessions()->where('expires_at', '>', now())->where('has_voted', false)->count() }}
                    </div>
                    <div>
                        <strong>Expired:</strong>
                        {{ $event->votingSessions()->where('expires_at', '<=', now())->where('has_voted', false)->count() }}
                    </div>
                </div>
            </div>

            <!-- Event Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Event Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Status:</strong>
                        @if($event->isActive())
                            <span class="badge bg-success">Active</span>
                        @elseif($event->hasEnded())
                            <span class="badge bg-secondary">Ended</span>
                        @else
                            <span class="badge bg-warning">Scheduled</span>
                        @endif
                    </div>
                    <div class="mb-2">
                        <strong>Start:</strong> {{ $event->start_time->format('M d, Y g:i A') }}
                    </div>
                    <div class="mb-2">
                        <strong>End:</strong> {{ $event->end_time->format('M d, Y g:i A') }}
                    </div>
                    <div>
                        <strong>Questions:</strong> {{ $event->questions()->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Generate QR code
            const qrContainer = document.getElementById('qr-code-container');
            const voteUrl = '{{ $qrCodeUrl }}';

            if (qrContainer && voteUrl) {
                new QRCode(qrContainer, {
                    text: voteUrl,
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        });

        function copyToClipboard() {
            const urlInput = document.getElementById('vote-url');
            urlInput.select();
            urlInput.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(urlInput.value);

            // Show feedback
            const copyBtn = document.querySelector('.btn-outline-secondary');
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied';
            copyBtn.classList.remove('btn-outline-secondary');
            copyBtn.classList.add('btn-success');

            setTimeout(() => {
                copyBtn.innerHTML = originalText;
                copyBtn.classList.remove('btn-success');
                copyBtn.classList.add('btn-outline-secondary');
            }, 2000);
        }
    </script>
</x-layouts.app>