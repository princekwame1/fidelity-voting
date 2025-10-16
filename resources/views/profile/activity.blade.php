<x-layouts.app title="Activity Log">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-muted"><i class="fas fa-history"></i> Activity Log</h4>
        <div>
            <a href="{{ route('profile.edit') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> Recent Activities
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($activities) > 0)
                        <div class="timeline">
                            @foreach($activities as $activity)
                                <div class="timeline-item mb-4">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="timeline-marker">
                                                @switch($activity['type'])
                                                    @case('event_created')
                                                        <i class="fas fa-plus-circle text-success"></i>
                                                        @break
                                                    @case('profile_updated')
                                                        <i class="fas fa-user-edit text-primary"></i>
                                                        @break
                                                    @case('password_changed')
                                                        <i class="fas fa-key text-warning"></i>
                                                        @break
                                                    @case('login')
                                                        <i class="fas fa-sign-in-alt text-info"></i>
                                                        @break
                                                    @default
                                                        <i class="fas fa-circle text-secondary"></i>
                                                @endswitch
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card border-left-0">
                                                <div class="card-body py-2">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1">
                                                                @switch($activity['type'])
                                                                    @case('event_created')
                                                                        Created Event: {{ $activity['event_name'] }}
                                                                        @break
                                                                    @case('profile_updated')
                                                                        Profile Updated
                                                                        @break
                                                                    @case('password_changed')
                                                                        Password Changed
                                                                        @break
                                                                    @case('login')
                                                                        Logged In
                                                                        @break
                                                                    @default
                                                                        {{ ucwords(str_replace('_', ' ', $activity['type'])) }}
                                                                @endswitch
                                                            </h6>
                                                            <p class="text-muted small mb-0">
                                                                {{ $activity['details'] }}
                                                            </p>
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center mt-4">
                            <button class="btn btn-primary" onclick="loadMoreActivity()">
                                <i class="fas fa-plus"></i> Load More
                            </button>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Activity Yet</h5>
                            <p class="text-muted">Your activities will appear here as you use the system.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Activity Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-pie"></i> Activity Summary
                    </h6>
                </div>
                <div class="card-body">
                    @if($user->isAdmin())
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Events Created:</span>
                                <strong>{{ $user->events()->count() ?? 0 }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Total Votes Received:</span>
                                <strong>{{ $user->events()->withCount('votes')->get()->sum('votes_count') ?? 0 }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Active Events:</span>
                                <strong>{{ $user->events()->where('start_time', '<=', now())->where('end_time', '>=', now())->count() ?? 0 }}</strong>
                            </div>
                        </div>
                    @endif
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Account Age:</span>
                            <strong>{{ $user->created_at->diffForHumans(null, true) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Last Login:</span>
                            <strong>{{ $user->updated_at->diffForHumans() }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Sessions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-desktop"></i> Security Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Account Security</small>
                        <div class="mt-1">
                            @if($user->email_verified_at)
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Email Verified
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Email Not Verified
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Password</small>
                        <div class="mt-1">
                            <span class="badge bg-info">
                                <i class="fas fa-shield-alt"></i> Last changed {{ $user->updated_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    @if($user->isAdmin())
                        <div class="mb-3">
                            <small class="text-muted">Admin Privileges</small>
                            <div class="mt-1">
                                <span class="badge bg-danger">
                                    <i class="fas fa-crown"></i> Administrator
                                </span>
                            </div>
                        </div>
                    @endif

                    <div class="d-grid">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Update Security Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .timeline-marker::after {
            content: '';
            position: absolute;
            top: 40px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 40px;
            background: #e9ecef;
            z-index: -1;
        }

        .timeline-item:last-child .timeline-marker::after {
            display: none;
        }

        .border-left-0 {
            border-left: 3px solid #007bff;
        }
    </style>

    <script>
        function loadMoreActivity() {
            // In a real implementation, this would load more activities via AJAX
            alert('Load more functionality would be implemented here');
        }
    </script>
</x-layouts.app>