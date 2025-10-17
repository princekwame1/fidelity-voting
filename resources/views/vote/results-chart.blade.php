<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }} - Live Results</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f27b33 0%, #f5b361 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Timer Styles */
        .timer-container {
            background: linear-gradient(135deg, #f27b33 0%, #f5b361 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 40px rgba(242, 123, 51, 0.2);
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .timer-header {
            text-align: center;
            color: white;
            margin-bottom: 20px;
        }

        .timer-header h2 {
            font-size: 2.5em;
            margin: 0 0 15px 0;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .timer-header p {
            font-size: 1.4em;
            opacity: 0.95;
            margin: 0;
            font-weight: 500;
        }

        .timer-display {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .time-unit {
            background: white;
            border-radius: 15px;
            padding: 25px;
            min-width: 120px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .time-unit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .time-value {
            font-size: 2em;
            font-weight: bold;
            color: #f27b33;
            line-height: 1;
            transition: all 0.3s ease;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .time-value.pulse-change {
            animation: pulseValue 0.5s ease;
        }

        @keyframes pulseValue {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
                color: #10b981;
            }
            100% {
                transform: scale(1);
                color: #f27b33;
            }
        }

        .time-label {
            font-size: 1.1em;
            color: #666;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        .timer-expired {
            background: linear-gradient(135deg, #f27b33 0%, #f5b361 100%) !important;
            animation: shake 0.5s ease;
            /* border: 3px solid #fff; */
            opacity: 0.9;
        }

        .timer-expired .timer-header h3 {
            color: #fff !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            animation: pulse-text 2s infinite;
        }

        .timer-expired .timer-header p {
            color: rgba(255,255,255,0.9) !important;
        }

        .timer-expired .time-unit {
            background: rgba(255,255,255,0.95) !important;
            border: 2px solid #f27b33;
        }

        .timer-expired .time-value {
            color: #f27b33 !important;
            font-weight: 900;
        }

        .timer-expired .time-label {
            color: #666 !important;
        }

        @keyframes pulse-text {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.05);
            }
        }


        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }


        .timer-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
            animation: warningPulse 2s infinite;
        }

        @keyframes warningPulse {
            0%, 100% {
                box-shadow: 0 15px 40px rgba(255, 193, 7, 0.2);
            }
            50% {
                box-shadow: 0 20px 50px rgba(255, 193, 7, 0.4);
            }
        }

        .timer-progress {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 20px;
        }

        .timer-progress-bar {
            height: 100%;
            background: white;
            border-radius: 3px;
            transition: width 1s linear;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            cursor: default;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(242, 123, 51, 0.15);
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #f27b33;
        }
        .stat-label {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .chart-section {
            padding: 25px;
            min-height: 600px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .chart-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(242, 123, 51, 0.05), transparent);
            transition: left 0.5s ease;
        }

        .chart-section:hover::before {
            left: 100%;
        }

        .chart-section:last-child {
            border-bottom: none;
        }
        .chart-title {
            font-size: 1.3em;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
            width: 100%;
        }
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .update-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 10px 20px;
            border-radius: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .update-indicator.updating {
            background: linear-gradient(135deg, #f27b33, #f5b361);
            color: white;
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(242, 123, 51, 0.3);
        }
        .pulse {
            width: 10px;
            height: 10px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
            position: relative;
        }

        .pulse::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: inherit;
            border-radius: 50%;
            animation: ripple 2s infinite;
        }

        @keyframes ripple {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            100% {
                transform: scale(3);
                opacity: 0;
            }
        }
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 1; }
            70% { transform: scale(1.3); opacity: 0.3; }
            100% { transform: scale(0.95); opacity: 1; }
        }
        .main-content-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
            margin: 40px 0;
            align-items: start;
        }
        .left-panel {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        .right-panel {
            min-height: 400px;
        }
        .qr-section {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .qr-container {
            width: 100%;
            max-width: 400px;
            text-align: center;
            min-height: 450px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .qr-code {
            width: 350px;
            height: 350px;
            margin: 0 auto 20px auto;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
        .scan-text {
            font-size: 1.2em;
            color: #333;
        }
        .vote-url {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            font-family: monospace;
            color: #f27b33;
        }
        .no-data {
            background: white;
            border-radius: 15px;
            padding: 40px;
            /* box-shadow: 0 10px 30px rgba(0,0,0,0.1); */
            text-align: center;
            color: #666;
            width: 100%;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.5s ease-out;
        }
        
        /* Combined card for QR and charts */
        .combined-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* NEW: Content grid for side-by-side layout */
        .content-grid {
            display: grid;
            grid-template-columns: 400px 4px 1fr;
            gap: 30px;
            align-items: start;
        }

        .vertical-divider {
            width: 4px;
            background: #edecec;
            height: 80%;
            align-self: center;
            border-radius: 2px;
        }

        /* Live Rankings Table */
        .rankings-section {
            margin-top: 40px;
        }


        .rankings-title {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: white;
            text-align: center;
        }

        .rankings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: slideUp 0.6s ease-out;
        }

        .rankings-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #dee2e6;
        }

        .rankings-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .rankings-table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        .rankings-table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .rankings-table tbody tr:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }

        .contestant-text {
            font-weight: 500;
        }

        .ranking-row {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .ranking-row:hover {
            background: linear-gradient(90deg, #fff8f3 0%, #fef5ee 100%);
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(242, 123, 51, 0.1);
        }

        .rank-number {
            font-weight: bold;
            font-size: 1.2em;
            color: #f27b33;
            width: 50px;
            text-align: center;
        }

        .option-text {
            font-weight: 500;
            color: #333;
        }


        .percentage {
            color: #666;
            text-align: center;
        }

        .question-header {
            background: #f27b33;
            color: white;
            font-size: 1.1em;
            text-align: center;
        }

        .rank-change {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8em;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.3s ease;
            animation: bounceIn 0.5s ease-out;
        }

        @keyframes bounceIn {
            0% {
                transform: translateY(-50%) scale(0);
            }
            50% {
                transform: translateY(-50%) scale(1.2);
            }
            100% {
                transform: translateY(-50%) scale(1);
            }
        }

        .rank-up {
            color: #10b981;
        }

        .rank-down {
            color: #ef4444;
        }

        .rank-change.show {
            opacity: 1;
        }

        .vote-count {
            font-weight: bold;
            color: #f27b33;
            text-align: center;
            transition: all 0.3s ease;
        }

        .vote-count.updated {
            animation: pulseNumber 0.6s ease;
        }

        @keyframes pulseNumber {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.3);
                color: #10b981;
            }
            100% {
                transform: scale(1);
                color: #f27b33;
            }
        }

        .chart-container canvas {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .data-update-flash {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(242, 123, 51, 0.1), transparent);
            pointer-events: none;
            animation: flash 1s ease-out;
        }

        @keyframes flash {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }
        
        /* Desktop-only message for non-desktop devices */
        .desktop-only-message {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #f27b33 0%, #f5b361 100%);
            color: white;
            z-index: 9999;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 3vh 3vw;
        }

        .desktop-message-content {
            max-width: 80vw;
        }

        .desktop-message-content .icon {
            font-size: 8vw;
            margin-bottom: 3vh;
        }

        .desktop-message-content h2 {
            font-size: 5vw;
            margin-bottom: 3vh;
            font-weight: 700;
        }

        .desktop-message-content p {
            font-size: 3vw;
            line-height: 1.6;
            margin-bottom: 2vh;
        }

        .desktop-message-content .requirement {
            background: rgba(255,255,255,0.1);
            padding: 2vh 3vw;
            border-radius: 15px;
            margin-top: 3vh;
            font-weight: 600;
        }

        @media (max-width: 1024px) {
            .desktop-only-message {
                display: flex;
            }
            .container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Desktop Only Message -->
    <div class="desktop-only-message">
        <div class="desktop-message-content">
            <div class="icon">🖥️</div>
            <h2>Desktop View Required</h2>
            <p>This voting results page is optimized for desktop viewing only.</p>
            <p>Please access this page from a desktop computer for the complete voting results experience.</p>
            <div class="requirement">
                <strong>Minimum Requirements:</strong><br>
                Desktop screen (1024px+ width)
            </div>
        </div>
    </div>

    <div class="container">
        <div class="update-indicator">
            <div class="pulse"></div>
            <span>Live Updates</span>
        </div>

        {{-- <div class="header">
            <h1>{{ $event->name }}</h1>
        </div> --}}

        <!-- Countdown Timer -->
        <div class="timer-container" id="timer-container">
            <div class="timer-header">
                <h3 style="font-size: 25px" id="timer-status">{{ strtoupper($event->name) }} - VOTING ENDS IN</h3>
                <p style="font-size: 18px" id="end-time-display">{{ strtoupper(\Carbon\Carbon::parse($event->end_time)->format('F j, Y \a\t g:i A')) }}</p>
            </div>
            <div class="timer-display" id="timer-display">
                <div class="time-unit">
                    <div class="time-value" id="days">0</div>
                    <div class="time-label">DAYS</div>
                </div>
                <div class="time-unit">
                    <div class="time-value" id="hours">0</div>
                    <div class="time-label">HOURS</div>
                </div>
                <div class="time-unit">
                    <div class="time-value" id="minutes">0</div>
                    <div class="time-label">MINUTES</div>
                </div>
                <div class="time-unit">
                    <div class="time-value" id="seconds">0</div>
                    <div class="time-label">SECONDS</div>
                </div>
            </div>
            <div class="timer-progress">
                <div class="timer-progress-bar" id="progress-bar"></div>
            </div>
        </div>

        <!-- QR Code and Charts Section - Combined Card -->
        <div class="combined-card">
            <div class="content-grid">
                <div class="qr-section">
                    <div class="qr-container">
                        <div id="qrcode" class="qr-code"></div>
                        <div class="scan-text">Scan to Vote</div>
                    </div>
                </div>

                <div class="vertical-divider"></div>

                <div class="charts-container" id="charts-container">
                    <!-- Charts will be dynamically inserted here -->
                    <div class="no-data">NO VOTING DATA YET. RESULTS WILL APPEAR HERE ONCE VOTING BEGINS.</div>
                </div>
            </div>
        </div>

        {{-- Debug: show_results_table = {{ $event->show_results_table ? 'true' : 'false' }} --}}
        @if($event->show_results_table)
        <!-- Live Rankings Table Section -->
        <div class="rankings-section">
            {{-- <h2 class="rankings-title">Live Rankings</h2> --}}
            <div id="rankings-container">
                <div class="no-data">NO VOTING DATA YET. RANKINGS WILL APPEAR HERE ONCE VOTING BEGINS.</div>
            </div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
    <script>
        // Generate QR Code
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ url('/vote/event/' . $event->encrypted_id) }}",
            width: 350,
            height: 350,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });



        // Function to get text before first dash
        function getTextBeforeDash(text) {
            const dashIndex = text.search(/[–-]/);
            if (dashIndex !== -1) {
                return text.substring(0, dashIndex).trim();
            }
            return text;
        }

        const eventId = {{ $event->id }};
        const charts = {};
        let previousRankings = new Map(); // Track previous rankings for animations
        let isFirstLoad = true; // Track first load to avoid initial animations

        // Timer variables
        const eventEndTime = new Date('{{ $event->end_time }}').getTime();
        const eventStartTime = new Date('{{ $event->start_time }}').getTime();
        let timerInterval;
        let previousTimeValues = {};

        // Chart colors - Primary and Secondary based
        const colors = [
            '#f27b33', '#f5b361', '#e85d1a', '#f19c3e', '#c24815',
            '#be6824', '#d4651f', '#e8932d', '#f4a373', '#f8c4a3'
        ];

        async function fetchResults() {
            try {
                // Show updating indicator
                const indicator = document.querySelector('.update-indicator');
                if (indicator && !isFirstLoad) {
                    indicator.classList.add('updating');
                }

                const response = await fetch(`/results/${eventId}/data`);
                const data = await response.json();
                console.log('Fetched results:', data);
                updateCharts(data.results);
                updateRankings(data.results);
                lastUpdateTime = Date.now(); // Update the last update time

                // Hide updating indicator after a short delay
                if (indicator) {
                    setTimeout(() => {
                        indicator.classList.remove('updating');
                    }, 500);
                }

                isFirstLoad = false;
            } catch (error) {
                console.error('Error fetching results:', error);
            }
        }

        function updateCharts(questions) {
            const container = document.getElementById('charts-container');

            if (!questions || questions.length === 0) {
                container.innerHTML = '<div class="no-data">NO VOTING DATA YET. RESULTS WILL APPEAR HERE ONCE VOTING BEGINS.</div>';
                return;
            }

            // Clear the "no data" message
            if (container.querySelector('.no-data')) {
                container.innerHTML = '';
            }

            questions.forEach((question, index) => {
                let chartCard = document.getElementById(`chart-${question.id}`);

                if (!chartCard) {
                    // Create new chart section
                    chartCard = document.createElement('div');
                    chartCard.className = 'chart-section';
                    chartCard.id = `chart-${question.id}`;
                    chartCard.innerHTML = `
                        <h3 class="chart-title" title="${question.question}">${getTextBeforeDash(question.question)}</h3>
                        <div class="chart-container">
                            <canvas id="canvas-${question.id}"></canvas>
                        </div>
                    `;
                    container.appendChild(chartCard);

                    // Create chart
                    const ctx = document.getElementById(`canvas-${question.id}`).getContext('2d');
                    charts[question.id] = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: question.options.map(opt => opt.text),
                            datasets: [{
                                label: 'Votes',
                                data: question.options.map(opt => opt.votes),
                                backgroundColor: colors.slice(0, question.options.length),
                                borderColor: colors.slice(0, question.options.length),
                                borderWidth: 2,
                                borderRadius: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    borderColor: '#f27b33',
                                    borderWidth: 1,
                                    cornerRadius: 8,
                                    padding: 12,
                                    displayColors: false,
                                    callbacks: {
                                        label: function(context) {
                                            const value = context.parsed.y;
                                            const percentage = question.options[context.dataIndex].percentage;
                                            return [`Votes: ${value}`, `Percentage: ${percentage}%`];
                                        }
                                    }
                                }
                            },
                            onHover: (event, activeElements) => {
                                event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    },
                                    grace: '10%' // Add some space at the top for labels
                                },
                                x: {
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: 45,
                                        minRotation: 0
                                    }
                                }
                            },
                            animation: {
                                duration: 1500,
                                easing: 'easeInOutCubic',
                                animateRotate: true,
                                animateScale: true,
                                onComplete: function(animation) {
                                    // Draw data labels after animation completes
                                    const chart = animation.chart;
                                    const ctx = chart.ctx;
                                    ctx.font = 'bold 14px sans-serif';
                                    ctx.fillStyle = '#333';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'bottom';

                                    chart.data.datasets.forEach((dataset, i) => {
                                        const meta = chart.getDatasetMeta(i);
                                        meta.data.forEach((bar, index) => {
                                            const data = dataset.data[index];
                                            if (data > 0) {
                                                const x = bar.x;
                                                const y = bar.y - 5;
                                                ctx.fillText(data, x, y);
                                            }
                                        });
                                    });
                                }
                            }
                        }
                    });
                } else {
                    // Update existing chart with animation
                    const chart = charts[question.id];
                    const oldData = [...chart.data.datasets[0].data];
                    const newData = question.options.map(opt => opt.votes);

                    // Check if data has changed
                    const hasChanged = oldData.some((val, idx) => val !== newData[idx]);

                    if (hasChanged) {
                        // Add flash effect to chart container
                        const container = chartCard.querySelector('.chart-container');
                        const flash = document.createElement('div');
                        flash.className = 'data-update-flash';
                        container.style.position = 'relative';
                        container.appendChild(flash);
                        setTimeout(() => flash.remove(), 1000);
                    }

                    chart.data.labels = question.options.map(opt => opt.text);
                    chart.data.datasets[0].data = newData;

                    // Update chart and redraw labels
                    chart.update('active');

                    // Redraw data labels after update
                    setTimeout(() => {
                        const ctx = chart.ctx;
                        ctx.font = 'bold 14px sans-serif';
                        ctx.fillStyle = '#333';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';

                        chart.data.datasets.forEach((dataset, i) => {
                            const meta = chart.getDatasetMeta(i);
                            meta.data.forEach((bar, index) => {
                                const data = dataset.data[index];
                                if (data > 0) {
                                    const x = bar.x;
                                    const y = bar.y - 5;
                                    ctx.fillText(data, x, y);
                                }
                            });
                        });
                    }, 100);
                }
            });
        }

        function updateRankings(questions) {
            const container = document.getElementById('rankings-container');

            // Skip if rankings container doesn't exist (when show_results_table is false)
            if (!container) {
                return;
            }

            if (!questions || questions.length === 0) {
                container.innerHTML = '<div class="no-data">NO VOTING DATA YET. RANKINGS WILL APPEAR HERE ONCE VOTING BEGINS.</div>';
                return;
            }

            // Clear the "no data" message
            if (container.querySelector('.no-data')) {
                container.innerHTML = '';
            }

            // Create combined rankings across all questions
            let allOptions = [];
            questions.forEach(question => {
                question.options.forEach(option => {
                    allOptions.push({
                        id: `${question.id}-${option.id}`,
                        questionText: question.question,
                        optionText: option.text,
                        optionSubtext: option.subtext || option.sub_text || '',  // Support both naming conventions
                        votes: option.votes || 0,
                        percentage: option.percentage || 0
                    });
                });
            });

            // Sort by votes (descending)
            allOptions.sort((a, b) => b.votes - a.votes);

            // Check if we need to create the table
            let table = container.querySelector('.rankings-table');
            if (!table) {
                table = document.createElement('table');
                table.className = 'rankings-table';
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th style="width: 60px;">RANK</th>
                            <th>CONTESTANT</th>
                            <th style="width: 80px; text-align: center;">VOTES</th>
                            <th style="width: 80px; text-align: center;">%</th>
                        </tr>
                    </thead>
                    <tbody id="rankings-tbody">
                    </tbody>
                `;
                container.appendChild(table);
            }

            const tbody = document.getElementById('rankings-tbody');

            // Track rank changes
            const currentRankings = new Map();
            allOptions.forEach((option, index) => {
                currentRankings.set(option.id, index + 1);
            });

            // Update table rows
            allOptions.forEach((option, index) => {
                const rank = index + 1;
                let row = document.getElementById(`rank-row-${option.id}`);

                if (!row) {
                    // Create new row with animation
                    row = document.createElement('tr');
                    row.className = 'ranking-row';
                    row.id = `rank-row-${option.id}`;
                    if (!isFirstLoad) {
                        row.style.animation = 'slideIn 0.5s ease-out';
                    }
                    tbody.appendChild(row);
                }

                // Check for rank change
                const previousRank = previousRankings.get(option.id);
                let rankChangeIndicator = '';

                if (previousRank && previousRank !== rank) {
                    if (rank < previousRank) {
                        rankChangeIndicator = '<span class="rank-change rank-up show">↗ UP</span>';
                    } else {
                        rankChangeIndicator = '<span class="rank-change rank-down show">↘ DOWN</span>';
                    }

                    // Hide indicator after 3 seconds
                    setTimeout(() => {
                        const indicator = row.querySelector('.rank-change');
                        if (indicator) {
                            indicator.classList.remove('show');
                        }
                    }, 3000);
                }

                // Check if votes changed
                const currentVotes = row.querySelector('.vote-count')?.textContent;
                const votesChanged = currentVotes && parseInt(currentVotes) !== option.votes;

                // Build contestant display with subtext in single line format: "Contestant - Subtext"
                const contestantDisplay = option.optionSubtext
                    ? `${option.optionText} - ${option.optionSubtext}`
                    : option.optionText;

                row.innerHTML = `
                    <td class="rank-number">${rank}</td>
                    <td class="contestant-text">${contestantDisplay}</td>
                    <td class="vote-count ${votesChanged ? 'updated' : ''}">${option.votes}</td>
                    <td class="percentage">${option.percentage}%</td>
                    ${rankChangeIndicator}
                `;

                // Remove the updated class after animation
                if (votesChanged) {
                    setTimeout(() => {
                        const voteCell = row.querySelector('.vote-count');
                        if (voteCell) voteCell.classList.remove('updated');
                    }, 600);
                }

                // Animate row position changes
                const targetIndex = index;
                const currentIndex = Array.from(tbody.children).indexOf(row);

                if (currentIndex !== targetIndex) {
                    // Animate row position change
                    row.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';

                    // Add a slight scale effect during move
                    row.style.transform = 'scale(1.02)';

                    setTimeout(() => {
                        // Remove row from current position
                        row.remove();

                        // Insert at correct position
                        if (targetIndex >= tbody.children.length) {
                            tbody.appendChild(row);
                        } else {
                            tbody.insertBefore(row, tbody.children[targetIndex]);
                        }

                        // Reset scale after move
                        setTimeout(() => {
                            row.style.transform = 'scale(1)';
                        }, 50);
                    }, 100);
                }
            });

            // Update previous rankings for next comparison
            previousRankings.clear();
            currentRankings.forEach((rank, id) => {
                previousRankings.set(id, rank);
            });
        }

        // Countdown Timer Function
        function updateTimer() {
            const now = new Date().getTime();
            const timerContainer = document.getElementById('timer-container');
            const timerStatus = document.getElementById('timer-status');
            const endTimeDisplay = document.getElementById('end-time-display');

            // Check if event hasn't started yet
            if (now < eventStartTime) {
                // Voting hasn't started - count down to start time
                const distanceToStart = eventStartTime - now;

                // Calculate time units until start
                const days = Math.floor(distanceToStart / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distanceToStart % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distanceToStart % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distanceToStart % (1000 * 60)) / 1000);

                // Update display
                updateTimeUnit('days', days);
                updateTimeUnit('hours', hours);
                updateTimeUnit('minutes', minutes);
                updateTimeUnit('seconds', seconds);

                // Update styling and text for pre-start state
                timerContainer.style.background = 'linear-gradient(135deg, rgba(242, 123, 51, 0.8) 0%, rgba(245, 179, 97, 0.8) 100%)';
                timerContainer.classList.remove('timer-warning', 'timer-expired');
                timerStatus.textContent = '{{ strtoupper($event->name) }} - VOTING STARTS IN';
                endTimeDisplay.textContent = 'STARTS: {{ strtoupper(\Carbon\Carbon::parse($event->start_time)->format("F j, Y \\a\\t g:i A")) }}';

                // Progress bar at 0% before start
                document.getElementById('progress-bar').style.width = '0%';

                return;
            }

            // Event has started - count down to end time
            const distanceToEnd = eventEndTime - now;
            const totalDuration = eventEndTime - eventStartTime;
            const elapsed = now - eventStartTime;

            if (distanceToEnd < 0) {
                // Voting has ended
                clearInterval(timerInterval);
                timerContainer.classList.add('timer-expired');
                timerContainer.classList.remove('timer-warning');
                timerStatus.textContent = '{{ strtoupper($event->name) }} - VOTING HAS ENDED';
                document.getElementById('days').textContent = '0';
                document.getElementById('hours').textContent = '0';
                document.getElementById('minutes').textContent = '0';
                document.getElementById('seconds').textContent = '0';
                document.getElementById('progress-bar').style.width = '100%';

                // Hide QR code and expand charts
                hideQRCodeAndExpandCharts();

                // Stop fetching results
                clearInterval(resultsInterval);
                return;
            }

            // Calculate time units until end
            const days = Math.floor(distanceToEnd / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distanceToEnd % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distanceToEnd % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distanceToEnd % (1000 * 60)) / 1000);

            // Update display with animation
            updateTimeUnit('days', days);
            updateTimeUnit('hours', hours);
            updateTimeUnit('minutes', minutes);
            updateTimeUnit('seconds', seconds);

            // Update styling for active voting
            timerContainer.style.background = 'linear-gradient(135deg, #f27b33 0%, #f5b361 100%)';
            timerContainer.style.opacity = '1';  // Reset opacity to full when voting is active
            timerStatus.textContent = '{{ strtoupper($event->name) }} - VOTING ENDS IN';
            endTimeDisplay.textContent = 'ENDS: {{ strtoupper(\Carbon\Carbon::parse($event->end_time)->format("F j, Y \\a\\t g:i A")) }}';

            // Update progress bar
            const progressPercentage = Math.min(100, (elapsed / totalDuration) * 100);
            document.getElementById('progress-bar').style.width = progressPercentage + '%';

            // Add warning class if less than 5 minutes remaining
            if (distanceToEnd < 5 * 60 * 1000 && !timerContainer.classList.contains('timer-warning')) {
                timerContainer.classList.add('timer-warning');
                timerStatus.textContent = '{{ strtoupper($event->name) }} - VOTING ENDING SOON!';
            }
        }

        function updateTimeUnit(unit, value) {
            const element = document.getElementById(unit);
            const currentValue = parseInt(element.textContent);

            if (currentValue !== value) {
                element.textContent = value;
                element.classList.add('pulse-change');
                setTimeout(() => {
                    element.classList.remove('pulse-change');
                }, 500);
            }
        }

        function hideQRCodeAndExpandCharts() {
            // Hide the QR section
            const qrSection = document.querySelector('.qr-section');
            if (qrSection) {
                qrSection.style.display = 'none';
            }

            // Expand the charts to cover full width
            const chartsContainer = document.querySelector('.charts-container');
            if (chartsContainer) {
                chartsContainer.style.width = '100%';
                chartsContainer.style.maxWidth = '100%';
            }

            // Remove the vertical divider
            const divider = document.querySelector('.vertical-divider');
            if (divider) {
                divider.style.display = 'none';
            }

            // Update the content grid to single column
            const contentGrid = document.querySelector('.content-grid');
            if (contentGrid) {
                contentGrid.style.gridTemplateColumns = '1fr';
                contentGrid.style.gap = '0';
            }


            // Resize all existing charts to fit the new container
            setTimeout(() => {
                Object.values(charts).forEach(chart => {
                    if (chart) {
                        chart.resize();
                    }
                });
            }, 100);
        }

        // Initial load
        fetchResults();
        updateTimer();

        // Start timer interval
        timerInterval = setInterval(updateTimer, 1000);

        // Auto-refresh results every 3 seconds
        const resultsInterval = setInterval(fetchResults, 3000);

        // Visual feedback for updates
        let lastUpdateTime = Date.now();
        setInterval(() => {
            const indicator = document.querySelector('.update-indicator');
            if (indicator) {
                if (Date.now() - lastUpdateTime < 4000) {
                    indicator.style.opacity = '1';
                } else {
                    indicator.style.opacity = '0.5';
                }
            }
        }, 100);

        // The voting status is now checked directly in updateTimer function
        // No need for separate checkVotingStatus as it's integrated into the timer update logic
    </script>
</body>
</html>