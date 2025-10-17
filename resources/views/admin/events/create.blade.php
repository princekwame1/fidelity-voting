<x-layouts.app title="Create New Event">
    <div class="row">
        <div class="col-lg-12 col-sm-12 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-muted">Create New Event</h4>
                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Events
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.events.store') }}" id="event-form">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Event Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" placeholder="Enter event name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3" placeholder="Enter event description (optional)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time *</label>
                                    <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror"
                                           id="start_time" name="start_time" value="{{ old('start_time') }}" placeholder="Select start date and time" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time *</label>
                                    <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror"
                                           id="end_time" name="end_time" value="{{ old('end_time') }}" placeholder="Select end date and time" required>
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
                                   value="{{ old('voting_duration_minutes', 30) }}" min="5" max="1440" placeholder="30" required>
                            <small class="text-muted">
                                How long each attendee has to complete their vote after scanning the QR code (5 minutes to 24 hours)
                            </small>
                            @error('voting_duration_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_results_table" value="1" id="show_results_table" checked>
                                <label class="form-check-label" for="show_results_table">
                                    Show results table on results page
                                </label>
                            </div>
                            <small class="text-muted">
                                When enabled, displays a ranking table with contestant names and vote counts on the results page
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="collect_emails" value="1" id="collect_emails">
                                <label class="form-check-label" for="collect_emails">
                                    Collect voter email addresses
                                </label>
                            </div>
                            <small class="text-muted">
                                When enabled, voters will be required to enter their email address before voting
                            </small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                            <h5>Questions</h5>
                            <button type="button" class="btn btn-primary btn-sm" onclick="addQuestion()">
                                <i class="fas fa-plus"></i> Add Question
                            </button>
                        </div>

                        <div id="questions-container">
                            <!-- Questions will be added here -->
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">
                                 Create Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let questionCount = 0;

        function addQuestion() {
            questionCount++;
            const container = document.getElementById('questions-container');
            const questionHtml = `
                <div class="card mb-3 question-card" id="question-${questionCount}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Question ${questionCount}</h6>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(${questionCount})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Question Text *</label>
                            <input type="text" class="form-control" name="questions[${questionCount}][text]" placeholder="Enter your question" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="questions[${questionCount}][multiple_choice]" value="1" id="multiple-${questionCount}">
                                <label class="form-check-label" for="multiple-${questionCount}">
                                    Allow multiple choices
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Options *</label>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="addOption(${questionCount})">
                                    <i class="fas fa-plus"></i> Add Option
                                </button>
                            </div>
                            <div class="options-container" id="options-${questionCount}">
                                <div class="option-group mb-3 border rounded p-2">
                                    <div class="mb-2">
                                        <input type="text" class="form-control" name="questions[${questionCount}][options][]" placeholder="Option 1" required>
                                    </div>
                                    <div>
                                        <input type="text" class="form-control form-control-sm" name="questions[${questionCount}][subtexts][]" placeholder="Subtext for Option 1 (optional)">
                                    </div>
                                </div>
                                <div class="option-group mb-3 border rounded p-2">
                                    <div class="mb-2">
                                        <input type="text" class="form-control" name="questions[${questionCount}][options][]" placeholder="Option 2" required>
                                    </div>
                                    <div>
                                        <input type="text" class="form-control form-control-sm" name="questions[${questionCount}][subtexts][]" placeholder="Subtext for Option 2 (optional)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', questionHtml);
        }

        function removeQuestion(questionId) {
            const questionCard = document.getElementById(`question-${questionId}`);
            if (questionCard) {
                questionCard.remove();
            }
        }

        function addOption(questionId) {
            const container = document.getElementById(`options-${questionId}`);
            const optionCount = container.children.length + 1;
            const optionHtml = `
                <div class="option-group mb-3 border rounded p-2">
                    <div class="mb-2">
                        <div class="input-group">
                            <input type="text" class="form-control" name="questions[${questionId}][options][]" placeholder="Option ${optionCount}" required>
                            <button type="button" class="btn btn-danger" onclick="this.closest('.option-group').remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <input type="text" class="form-control form-control-sm" name="questions[${questionId}][subtexts][]" placeholder="Subtext for Option ${optionCount} (optional)">
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', optionHtml);
        }

        // Initialize with one question
        document.addEventListener('DOMContentLoaded', function() {
            addQuestion();

            // Set minimum datetime to now
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            const isoString = now.toISOString().slice(0, 16);
            document.getElementById('start_time').min = isoString;
            document.getElementById('end_time').min = isoString;
        });

        // Update end time minimum when start time changes
        document.getElementById('start_time').addEventListener('change', function() {
            document.getElementById('end_time').min = this.value;
        });
    </script>
</x-layouts.app>