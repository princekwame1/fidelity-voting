<x-layouts.app title="Edit Event - {{ $event->name }}">
    <div class="row">
        <div class="col-lg-12 col-sm-12 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-muted">Edit Event</h4>
                <a href="{{ route('admin.events.show', $event) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Event
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.events.update', $event) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Event Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $event->name) }}" placeholder="Enter event name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3" placeholder="Enter event description (optional)">{{ old('description', $event->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time *</label>
                                    <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror"
                                           id="start_time" name="start_time"
                                           value="{{ old('start_time', $event->start_time->format('Y-m-d\TH:i')) }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time *</label>
                                    <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror"
                                           id="end_time" name="end_time"
                                           value="{{ old('end_time', $event->end_time->format('Y-m-d\TH:i')) }}" required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="voting_duration_minutes" class="form-label">
                               Voting Session Duration (Minutes) *
                            </label>
                            <input type="number" class="form-control @error('voting_duration_minutes') is-invalid @enderror"
                                   id="voting_duration_minutes" name="voting_duration_minutes"
                                   value="{{ old('voting_duration_minutes', $event->voting_duration_minutes) }}" min="5" max="1440" required>
                            <small class="text-muted">
                                How long each attendee has to complete their vote after scanning the QR code (5 minutes to 24 hours)
                            </small>
                            @error('voting_duration_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_results_table" value="1"
                                       id="show_results_table" {{ old('show_results_table', $event->show_results_table) ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_results_table">
                                    Show results table on results page
                                </label>
                            </div>
                            <small class="text-muted">
                                When enabled, displays a ranking table with contestant names and vote counts on the results page
                            </small>
                        </div>

                        @if($event->votes()->count() > 0)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Note:</strong> This event has existing votes. You can only edit basic information.
                                Questions and options cannot be modified after voting has begun.
                            </div>
                        @endif


                        <div class="mb-4">
                            <h5>Questions</h5>
                            @foreach($event->questions as $question)
                                <div class="card mb-3">
                                    <div class="card-header">
                                        Question {{ $loop->iteration }}
                                    </div>
                                    <div class="card-body">
                                        <h6>{{ $question->question_text }}</h6>
                                        <small class="text-muted">
                                            {{ $question->multiple_choice ? 'Multiple choice' : 'Single choice' }}
                                        </small>
                                        <ul class="mt-2">
                                            @foreach($question->options as $option)
                                                <li>{{ $option->option_text }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                            <p class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Questions and options cannot be edited after the event is created.
                            </p>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">
                                Update Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update end time minimum when start time changes
            document.getElementById('start_time').addEventListener('change', function() {
                document.getElementById('end_time').min = this.value;
            });
        });
    </script>
</x-layouts.app>