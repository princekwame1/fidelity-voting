<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }} - Voting</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js'])
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .event-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .event-title {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .event-description {
            color: #666;
            margin: 0;
            font-size: 16px;
        }
        .question {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background: #fafafa;
        }
        .question-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            font-size: 18px;
        }
        .question-type {
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
        }
        .option {
            margin-bottom: 10px;
        }
        .option label {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .option label:hover {
            background-color: #f0f0f0;
        }
        .option input[type="radio"],
        .option input[type="checkbox"] {
            margin-right: 12px;
            transform: scale(1.2);
        }
        .submit-btn {
            width: 100%;
            padding: 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .submit-btn:hover {
            background-color: #0056b3;
        }
        .submit-btn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        .error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .loading {
            text-align: center;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="event-header">
            <h1 class="event-title">{{ $event->name }}</h1>
            @if($event->description)
                <p class="event-description">{{ $event->description }}</p>
            @endif
        </div>

        <div id="alert-container"></div>

        <form id="voting-form">
            @csrf
            @foreach($questions as $question)
                <div class="question" data-question-id="{{ $question->id }}">
                    <h3 class="question-title">{{ $question->question_text }}</h3>
                    <div class="question-type">
                        {{ $question->multiple_choice ? 'Multiple choice (select all that apply)' : 'Single choice' }}
                    </div>

                    @foreach($question->options as $option)
                        <div class="option">
                            <label>
                                <input type="{{ $question->multiple_choice ? 'checkbox' : 'radio' }}"
                                       name="answers[{{ $question->id }}]{{ $question->multiple_choice ? '[]' : '' }}"
                                       value="{{ $option->id }}"
                                       {{ $question->multiple_choice ? '' : 'required' }}>
                                {{ $option->option_text }}
                            </label>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <input type="hidden" id="session_token" value="{{ $session_token }}">
            <button type="submit" class="submit-btn" id="submit-button">
                Submit Your Vote
            </button>
        </form>
    </div>

    <script>
        {!! $fingerprint_code !!}

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('voting-form');
            const submitButton = document.getElementById('submit-button');
            const alertContainer = document.getElementById('alert-container');

            function showAlert(message, type = 'error') {
                alertContainer.innerHTML = `<div class="${type}">${message}</div>`;
                alertContainer.scrollIntoView({ behavior: 'smooth' });
            }

            function hideAlerts() {
                alertContainer.innerHTML = '';
            }

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                hideAlerts();

                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';

                try {
                    const formData = new FormData(form);
                    const answers = {};

                    // Process form data into the expected format
                    @foreach($questions as $question)
                        @if($question->multiple_choice)
                            const question{{ $question->id }}Values = formData.getAll('answers[{{ $question->id }}][]');
                            if (question{{ $question->id }}Values.length > 0) {
                                answers['{{ $question->id }}'] = question{{ $question->id }}Values;
                            }
                        @else
                            const question{{ $question->id }}Value = formData.get('answers[{{ $question->id }}]');
                            if (question{{ $question->id }}Value) {
                                answers['{{ $question->id }}'] = question{{ $question->id }}Value;
                            }
                        @endif
                    @endforeach

                    // Add device fingerprint data and session token
                    const fingerprintData = await generateDeviceFingerprint();
                    const sessionToken = document.getElementById('session_token').value;

                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            answers: answers,
                            session_token: sessionToken,
                            fingerprint_data: fingerprintData
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        showAlert('Your vote has been submitted successfully! Thank you for participating.', 'success');
                        form.style.display = 'none';
                        setTimeout(() => {
                            window.location.href = '{{ route("vote.success") }}';
                        }, 2000);
                    } else {
                        showAlert(data.error || 'An error occurred while submitting your vote.');
                        submitButton.disabled = false;
                        submitButton.textContent = 'Submit Your Vote';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('An unexpected error occurred. Please try again.');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit Your Vote';
                }
            });
        });
    </script>
</body>
</html>