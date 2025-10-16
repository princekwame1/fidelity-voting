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
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
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
            min-height: 400px;
            border-bottom: 1px solid #eee;
        }

        .chart-section:last-child {
            border-bottom: none;
        }
        .chart-title {
            font-size: 1.3em;
            margin-bottom: 20px;
            color: #333;
        }
        .chart-container {
            position: relative;
            height: 300px;
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
        }
        .pulse {
            width: 10px;
            height: 10px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
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
            align-items: flex-start;
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
        }
        
        /* Combined card for QR and charts */
        .combined-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
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
            height: 100%;
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

        .ranking-row {
            transition: all 0.5s ease-in-out;
            position: relative;
        }

        .ranking-row:hover {
            background: #f8f9fa;
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

        .vote-count {
            font-weight: bold;
            color: #f27b33;
            text-align: center;
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
        
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            .vertical-divider {
                display: none;
            }
            .qr-section {
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 1.8em;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            .stat-card {
                padding: 15px;
            }
            .stat-number {
                font-size: 1.5em;
            }
            .qr-code {
                width: 200px;
                height: 200px;
            }
            .qr-container {
                padding: 20px;
                max-width: 300px;
            }
        }
        @media (max-width: 768px) {
            .update-indicator {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
            .header h1 {
                font-size: 1.5em;
            }
            .header p {
                font-size: 1em;
            }
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .stat-card {
                padding: 12px;
            }
            .stat-number {
                font-size: 1.2em;
            }
            .qr-code {
                width: 150px;
                height: 150px;
            }
            .qr-container {
                padding: 15px;
            }
            .scan-text {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="update-indicator">
            <div class="pulse"></div>
            <span>Live Updates</span>
        </div>

        <div class="header">
            <h1>{{ $event->name }}</h1>
            @if($event->description)
                <p>{{ $event->description }}</p>
            @endif
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
                    <div class="no-data">No voting data yet. Results will appear here once voting begins.</div>
                </div>
            </div>
        </div>

        <!-- Live Rankings Table Section -->
        <div class="rankings-section">
            {{-- <h2 class="rankings-title">Live Rankings</h2> --}}
            <div id="rankings-container">
                <div class="no-data">No voting data yet. Rankings will appear here once voting begins.</div>
            </div>
        </div>
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

        const eventId = {{ $event->id }};
        const charts = {};
        let previousRankings = new Map(); // Track previous rankings for animations

        // Chart colors - Primary and Secondary based
        const colors = [
            '#f27b33', '#f5b361', '#e85d1a', '#f19c3e', '#c24815',
            '#be6824', '#d4651f', '#e8932d', '#f4a373', '#f8c4a3'
        ];

        async function fetchResults() {
            try {
                const response = await fetch(`/results/${eventId}/data`);
                const data = await response.json();
                console.log('Fetched results:', data);
                updateCharts(data.results);
                updateRankings(data.results);
                lastUpdateTime = Date.now(); // Update the last update time
            } catch (error) {
                console.error('Error fetching results:', error);
            }
        }

        function updateCharts(questions) {
            const container = document.getElementById('charts-container');

            if (!questions || questions.length === 0) {
                container.innerHTML = '<div class="no-data">No voting data yet. Results will appear here once voting begins.</div>';
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
                        <h3 class="chart-title">${question.question}</h3>
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
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const value = context.parsed.y;
                                            const percentage = question.options[context.dataIndex].percentage;
                                            return `${value} votes (${percentage}%)`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
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
                                duration: 1000,
                                easing: 'easeInOutQuart'
                            }
                        }
                    });
                } else {
                    // Update existing chart
                    const chart = charts[question.id];
                    chart.data.labels = question.options.map(opt => opt.text);
                    chart.data.datasets[0].data = question.options.map(opt => opt.votes);
                    chart.update('active');
                }
            });
        }

        function updateRankings(questions) {
            const container = document.getElementById('rankings-container');

            if (!questions || questions.length === 0) {
                container.innerHTML = '<div class="no-data">No voting data yet. Rankings will appear here once voting begins.</div>';
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
                            <th style="width: 60px;">Rank</th>
                            <th>Question</th>
                            <th>Option</th>
                            <th style="width: 80px; text-align: center;">Votes</th>
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
                    // Create new row
                    row = document.createElement('tr');
                    row.className = 'ranking-row';
                    row.id = `rank-row-${option.id}`;
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

                row.innerHTML = `
                    <td class="rank-number">${rank}</td>
                    <td class="option-text" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">${option.questionText}</td>
                    <td class="option-text">${option.optionText}</td>
                    <td class="vote-count">${option.votes}</td>
                    <td class="percentage">${option.percentage}%</td>
                    ${rankChangeIndicator}
                `;

                // Animate row position changes
                const targetIndex = index;
                const currentIndex = Array.from(tbody.children).indexOf(row);

                if (currentIndex !== targetIndex) {
                    // Remove row from current position
                    row.remove();

                    // Insert at correct position
                    if (targetIndex >= tbody.children.length) {
                        tbody.appendChild(row);
                    } else {
                        tbody.insertBefore(row, tbody.children[targetIndex]);
                    }
                }
            });

            // Update previous rankings for next comparison
            previousRankings.clear();
            currentRankings.forEach((rank, id) => {
                previousRankings.set(id, rank);
            });
        }

        // Initial load
        fetchResults();

        // Auto-refresh every 3 seconds
        setInterval(fetchResults, 3000);

        // Visual feedback for updates
        let lastUpdateTime = Date.now();
        setInterval(() => {
            const indicator = document.querySelector('.update-indicator');
            if (Date.now() - lastUpdateTime < 4000) {
                indicator.style.opacity = '1';
            } else {
                indicator.style.opacity = '0.5';
            }
        }, 100);
    </script>
</body>
</html>