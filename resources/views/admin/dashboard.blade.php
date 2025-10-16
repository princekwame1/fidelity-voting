<x-layouts.app>
    <x-slot name="title">Admin Dashboard</x-slot>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-muted">Admin Dashboard</h4>
                <div>
                    {{-- <span class="badge bg-danger fs-6 me-2">Administrator</span> --}}
                    <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                        New Event
                    </a>
                </div>
            </div>

            <!-- Event Statistics -->
            <div class="row mb-4">
                <div class="col-6 col-md-3">
                    <div class="card text-dark bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-muted">Total Events</h5>
                                    <h2 class="card-text" style="color: #f27b33;">{{ \App\Models\Event::count() }}</h2>
                                </div>
                                <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-dark bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-muted">Active Events</h5>
                                    <h2 class="card-text" style="color: #f27b33;">{{ \App\Models\Event::whereDate('start_time', '<=', now())->whereDate('end_time', '>=', now())->count() }}</h2>
                                </div>
                                <i class="fas fa-play-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-dark bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-muted">Total Votes</h5>
                                    <h2 class="card-text" style="color: #f27b33;">{{ \App\Models\Vote::count() }}</h2>
                                </div>
                                <i class="fas fa-vote-yea fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-dark bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-muted">Participants</h5>
                                    <h2 class="card-text" style="color: #f27b33;">{{ \App\Models\VotingSession::where('has_voted', true)->count() }}</h2>
                                </div>
                                <i class="fas fa-users fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="text-muted">Recent Events</h4>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-list"></i> View All Events
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $recentEvents = \App\Models\Event::with(['questions', 'votes', 'votingSessions'])
                            ->withCount(['votes', 'votingSessions'])
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp

                    @if($recentEvents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Status</th>
                                        <th>Votes</th>
                                        <th>Participants</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentEvents as $event)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $event->name }}</strong>
                                                    @if($event->description)
                                                        <br><small class="text-muted">{{ Str::limit($event->description, 40) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($event->isActive())
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-play"></i> Active
                                                    </span>
                                                @elseif($event->hasEnded())
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-stop"></i> Ended
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> Scheduled
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $event->votes_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $event->voting_sessions_count }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.events.show', $event) }}"
                                                       class="btn btn-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('vote.results', $event) }}"
                                                       class="btn btn-success" target="_blank" title="Live Results">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                    @if($event->isActive())
                                                        <button class="btn btn-info btn-qr"
                                                                data-event-id="{{ $event->id }}"
                                                                data-event-name="{{ $event->name }}" title="Show QR Code">
                                                            <i class="fas fa-qrcode"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                            <h5>No Events Yet</h5>
                            <p class="text-muted">Create your first voting event to get started.</p>
                            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create New Event
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="text-muted">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create New Event
                                </a>
                                <a href="{{ route('admin.events.index') }}" class="btn btn-primary">
                                    <i class="fas fa-list me-2"></i>Manage Events
                                </a>
                                <button class="btn btn-info" onclick="showActiveEventsQR()">
                                    <i class="fas fa-qrcode me-2"></i>Show Active QR Codes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="text-muted">System Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-primary">{{ \App\Models\Question::count() }}</h4>
                                    <small class="text-muted">Total Questions</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success">{{ \App\Models\Option::count() }}</h4>
                                    <small class="text-muted">Total Options</small>
                                </div>
                            </div>
                            <div class="my-3"></div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-info">{{ \App\Models\User::count() }}</h4>
                                    <small class="text-muted">Total Users</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-warning">{{ \App\Models\User::where('role', 'admin')->count() }}</h4>
                                    <small class="text-muted">Admins</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-qrcode me-2"></i>QR Code for <span id="qr-event-name"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qr-code-container" class="mb-3"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Scan this QR code to vote</strong><br>
                        <small>Share this with event participants</small>
                    </div>
                    <div class="input-group">
                        <input type="text" class="form-control" id="vote-url" readonly>
                        <button class="btn btn-secondary" type="button" onclick="copyToClipboard()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
    <script>
        let currentQRCode = null;

        // Handle QR code button clicks
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-qr')) {
                const btn = e.target.closest('.btn-qr');
                const eventId = btn.getAttribute('data-event-id');
                const eventName = btn.getAttribute('data-event-name');
                showQRCode(eventId, eventName);
            }
        });

        function showQRCode(eventId, eventName) {
            const modal = new bootstrap.Modal(document.getElementById('qrModal'));
            const container = document.getElementById('qr-code-container');
            const nameSpan = document.getElementById('qr-event-name');
            const urlInput = document.getElementById('vote-url');

            // Clear previous QR code
            container.innerHTML = '';

            // Set event name
            nameSpan.textContent = eventName;

            // Generate voting URL
            const voteUrl = window.location.origin + '/vote/event/' + eventId;
            urlInput.value = voteUrl;

            // Generate QR code
            currentQRCode = new QRCode(container, {
                text: voteUrl,
                width: 256,
                height: 256,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            modal.show();
        }

        function showActiveEventsQR() {
            // You can implement this to show all active events with their QR codes
            alert('Feature coming soon: Show all active event QR codes');
        }

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