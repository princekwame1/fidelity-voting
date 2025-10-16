<x-layouts.app title="{{ $event->name }} - Event Details">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-muted">{{ $event->name }}</h4>
        <div>
            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit Event
            </a>
            <a href="{{ route('admin.events.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Events
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Event Info -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Event Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3"><strong>Status:</strong></div>
                        <div class="col-sm-9">
                            @if($event->isActive())
                                <span class="badge bg-success">Active</span>
                            @elseif($event->hasEnded())
                                <span class="badge bg-secondary">Ended</span>
                            @else
                                <span class="badge bg-warning">Scheduled</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3"></div>
                    @if($event->description)
                        <div class="row">
                            <div class="col-sm-3"><strong>Description:</strong></div>
                            <div class="col-sm-9">{{ $event->description }}</div>
                        </div>
                        <div class="mt-3"></div>
                    @endif
                    <div class="row">
                        <div class="col-sm-3"><strong>Start Time:</strong></div>
                        <div class="col-sm-9">{{ $event->start_time->format('M d, Y \a\t g:i A') }}</div>
                    </div>
                    <div class="mt-3"></div>
                    <div class="row">
                        <div class="col-sm-3"><strong>End Time:</strong></div>
                        <div class="col-sm-9">{{ $event->end_time->format('M d, Y \a\t g:i A') }}</div>
                    </div>
                    <div class="mt-3"></div>
                    <div class="row">
                        <div class="col-sm-3"><strong>Session Duration:</strong></div>
                        <div class="col-sm-9">
                             {{ $event->voting_duration_minutes }} minutes
                            <small class="text-muted">(per attendee)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Questions ({{ $event->questions->count() }})</h5>
                </div>
                <div class="card-body">
                    @foreach($event->questions as $index => $question)
                        <div class="question-item mb-4 @if(!$loop->last) border-bottom pb-4 @endif">
                            <h6>{{ $index + 1 }}. {{ $question->question_text }}</h6>
                            <small class="text-muted">
                                {{ $question->multiple_choice ? 'Multiple choice' : 'Single choice' }}
                            </small>
                            <div class="mt-2">
                                @foreach($question->options as $option)
                                    <div class="option-item d-flex justify-content-between align-items-center py-1">
                                        <span>• {{ $option->option_text }}</span>
                                        @if(isset($results['questions'][$loop->parent->index]['options'][$loop->index]))
                                            <small class="text-muted">
                                                {{ $results['questions'][$loop->parent->index]['options'][$loop->index]['votes'] }} votes
                                                ({{ $results['questions'][$loop->parent->index]['options'][$loop->index]['percentage'] }}%)
                                            </small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Stats & Actions -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $stats['votes']['total'] }}</h4>
                                <small class="text-muted">Total Votes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $stats['sessions']['voted'] }}</h4>
                            <small class="text-muted">Participants</small>
                        </div>
                    </div>
                    <div class="mt-3"></div>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-info">{{ $stats['sessions']['total'] }}</h4>
                                <small class="text-muted">Total Sessions</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning">{{ $stats['sessions']['active'] }}</h4>
                            <small class="text-muted">Active Sessions</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.events.qrcodes.index', $event) }}" class="btn btn-primary">
                            <i class="fas fa-qrcode"></i> Manage QR Codes
                        </a>
                        <a href="{{ route('vote.results', $event) }}" class="btn btn-success" target="_blank">
                            <i class="fas fa-chart-bar"></i> View Public Results
                        </a>
                        <hr>
                        <div class="text-center">
                            <p class="text-muted mb-2">Single QR Code for Event:</p>
                            <code>{{ url('/vote/event/' . $event->encrypted_id) }}</code>
                            <br>
                            <small class="text-muted">All attendees scan the same QR code</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Token Usage -->
            @if($stats['sessions']['total'] > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Session Usage</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $stats['sessions']['usage_rate'] }}%"
                                 aria-valuenow="{{ $stats['sessions']['usage_rate'] }}"
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ $stats['sessions']['usage_rate'] }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $stats['sessions']['voted'] }} of {{ $stats['sessions']['total'] }} sessions used
                        </small>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>