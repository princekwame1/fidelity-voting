<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $results['event']['name'] }} - Results</title>
    @vite(['resources/js/app.js'])
    <style>
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #f27b33 0%, #f5b361 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
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
            font-size: 28px;
        }
        .event-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0;
        }
        .stat {
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #f27b33;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        .question-result {
            margin-bottom: 40px;
            padding: 25px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: #fafafa;
        }
        .question-title {
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            font-size: 20px;
        }
        .question-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        .option-result {
            margin-bottom: 15px;
            background: white;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        .option-header {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 12px 15px;
            background: #f8f9fa;
        }
        .option-text {
            font-weight: 500;
            flex: 1;
        }
        .option-stats {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: #666;
        }
        .progress-bar {
            height: 8px;
            background: #e9ecef;
            position: relative;
            margin: 0 15px 12px 15px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #f27b33, #f5b361);
            transition: width 1s ease-in-out;
            border-radius: 4px;
        }
        .no-votes {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 6px;
            margin: 20px 0;
        }
        @media (max-width: 600px) {
            .event-stats {
                flex-direction: column;
                gap: 15px;
            }
            .option-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="event-header">
            <h1 class="event-title">{{ $results['event']['name'] }}</h1>
            @if($results['event']['description'])
                <p style="color: #666; margin: 10px 0;">{{ $results['event']['description'] }}</p>
            @endif

            <div class="event-stats">
                <div class="stat">
                    <div class="stat-number">{{ $results['event']['total_votes'] }}</div>
                    <div class="stat-label">Total Votes</div>
                </div>
                <div class="stat">
                    <div class="stat-number">{{ $results['event']['used_tokens'] }}</div>
                    <div class="stat-label">Participants</div>
                </div>
                <div class="stat">
                    <div class="stat-number">{{ count($results['questions']) }}</div>
                    <div class="stat-label">Questions</div>
                </div>
            </div>
        </div>

        @if(empty($results['questions']) || $results['event']['total_votes'] === 0)
            <div class="no-votes">
                <h3>No votes yet</h3>
                <p>Results will appear here once voting begins.</p>
            </div>
        @else
            @foreach($results['questions'] as $question)
                <div class="question-result">
                    <h3 class="question-title">{{ $question['text'] }}</h3>
                    <div class="question-meta">
                        {{ $question['multiple_choice'] ? 'Multiple choice' : 'Single choice' }} •
                        {{ $question['total_votes'] }} {{ $question['total_votes'] === 1 ? 'vote' : 'votes' }}
                    </div>

                    @foreach($question['options'] as $option)
                        <div class="option-result">
                            <div class="option-header">
                                <div class="option-text">{{ $option['text'] }}</div>
                                <div class="option-stats">
                                    <span>{{ $option['votes'] }} votes</span>
                                    <span>{{ $option['percentage'] }}%</span>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ $option['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
            <p style="color: #666; font-size: 14px;">
                Results are updated in real-time • Last updated: {{ now()->format('M d, Y \a\t g:i A') }}
            </p>
        </div>
    </div>

    <script>
        // Animate progress bars on load
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });

        // Auto-refresh results every 30 seconds
        setInterval(() => {
            if (document.hidden) return; // Don't refresh if tab is not active
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>