<x-layouts.app title="Events Management">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-muted">Events Management</h4>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Event
        </a>
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

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">All Events</h5>
        </div>
        <div class="card-body">
            @if($events->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Status</th>
                                <th>Dates</th>
                                <th>Session Duration</th>
                                <th>Questions</th>
                                <th>Votes</th>
                                <th>Sessions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                                <tr>
                                    <td>
                                        <strong>{{ $event->name }}</strong>
                                        @if($event->description)
                                            <br><small class="text-muted">{{ Str::limit($event->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($event->isActive())
                                            <span class="badge bg-success">Active</span>
                                        @elseif($event->hasEnded())
                                            <span class="badge bg-secondary">Ended</span>
                                        @else
                                            <span class="badge bg-warning">Scheduled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Start:</strong> {{ $event->start_time->format('M d, Y g:i A') }}<br>
                                            <strong>End:</strong> {{ $event->end_time->format('M d, Y g:i A') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $event->voting_duration_minutes ?? 30 }} min</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $event->questions_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $event->votes_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $event->voting_sessions_count }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.events.show', $event) }}" class="btn btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.events.qrcodes.index', $event) }}" class="btn btn-info">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                            <a href="{{ route('vote.results', $event) }}" class="btn btn-success" target="_blank">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $events->links() }}
                </div>
            @else
                <div class="text-center py-5">
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
</x-layouts.app>