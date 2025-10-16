<x-layouts.app title="Suspicious Activity">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-exclamation-triangle text-warning"></i> Suspicious Activity</h1>
        <div>
            <a href="{{ route('admin.security.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Security Dashboard
            </a>
        </div>
    </div>

    <!-- Activity Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" id="severityFilter">
                        <option value="">All Severities</option>
                        <option value="high">High Risk</option>
                        <option value="medium">Medium Risk</option>
                        <option value="low">Low Risk</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="multiple_devices_same_ip">Multiple Devices - Same IP</option>
                        <option value="bot_pattern_detected">Bot Pattern</option>
                        <option value="rapid_voting">Rapid Voting</option>
                        <option value="device_fingerprint_mismatch">Device Mismatch</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="timeFilter">
                        <option value="24h">Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" onclick="refreshData()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspicious Activities List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Recent Suspicious Activities
            </h5>
        </div>
        <div class="card-body">
            @if(count($activities) > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Type</th>
                                <th>Severity</th>
                                <th>Event</th>
                                <th>IP Address</th>
                                <th>Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                                <tr>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($activity['detected_at'])->format('M d, Y') }}</small><br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($activity['detected_at'])->format('g:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucwords(str_replace('_', ' ', $activity['type'])) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $severityClass = match($activity['severity']) {
                                                'high' => 'danger',
                                                'medium' => 'warning',
                                                'low' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $severityClass }}">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ ucfirst($activity['severity']) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(isset($activity['event_id']))
                                            <a href="{{ route('admin.events.show', $activity['event_id']) }}" class="text-decoration-none">
                                                Event #{{ $activity['event_id'] }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code>{{ $activity['ip_address'] ?? 'N/A' }}</code>
                                    </td>
                                    <td>
                                        @switch($activity['type'])
                                            @case('multiple_devices_same_ip')
                                                <small>{{ $activity['device_count'] ?? 'Multiple' }} devices detected</small>
                                                @break
                                            @case('bot_pattern_detected')
                                                <small>User Agent: <code>{{ Str::limit($activity['user_agent'] ?? 'Unknown', 30) }}</code></small>
                                                @break
                                            @case('rapid_voting')
                                                <small>{{ $activity['votes_per_minute'] ?? 'High' }} votes/minute</small>
                                                @break
                                            @default
                                                <small class="text-muted">Suspicious pattern detected</small>
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if(isset($activity['ip_address']))
                                                <button class="btn btn-danger"
                                                        onclick="blockIp('{{ $activity['ip_address'] }}')"
                                                        title="Block IP">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endif
                                            <button class="btn btn-info"
                                                    onclick="viewDetails({{ json_encode($activity) }})"
                                                    title="View Details">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                    <h5 class="text-success">No Suspicious Activity Detected</h5>
                    <p class="text-muted">Your voting system is secure and running smoothly.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Activity Details Modal -->
    <div class="modal fade" id="activityDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Activity Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="activityDetailsContent">
                        <!-- Details will be populated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="blockIpFromModal" onclick="blockIpFromDetails()">
                        <i class="fas fa-ban"></i> Block IP
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Block IP Modal -->
    <div class="modal fade" id="blockIpModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Block IP Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="blockIpForm">
                        @csrf
                        <input type="hidden" id="blockIpAddress" name="ip">

                        <div class="mb-3">
                            <label class="form-label">IP Address:</label>
                            <div class="form-control-plaintext" id="displayIpAddress"></div>
                        </div>

                        <div class="mb-3">
                            <label for="blockReason" class="form-label">Reason:</label>
                            <select class="form-control" id="blockReason" name="reason" required>
                                <option value="">Select reason...</option>
                                <option value="Suspicious voting pattern">Suspicious voting pattern</option>
                                <option value="Multiple devices from same IP">Multiple devices from same IP</option>
                                <option value="Bot behavior detected">Bot behavior detected</option>
                                <option value="Rapid voting detected">Rapid voting detected</option>
                                <option value="Manual security review">Manual security review</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="blockDuration" class="form-label">Duration (hours):</label>
                            <select class="form-control" id="blockDuration" name="duration_hours" required>
                                <option value="1">1 hour</option>
                                <option value="6">6 hours</option>
                                <option value="24">24 hours</option>
                                <option value="72">3 days</option>
                                <option value="168">1 week</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmBlockIp()">Block IP</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentActivityData = null;

        function refreshData() {
            // In a real implementation, this would fetch fresh data
            location.reload();
        }

        function blockIp(ip) {
            document.getElementById('blockIpAddress').value = ip;
            document.getElementById('displayIpAddress').textContent = ip;

            // Pre-select appropriate reason based on context
            const reasonSelect = document.getElementById('blockReason');
            reasonSelect.value = 'Suspicious voting pattern';

            new bootstrap.Modal(document.getElementById('blockIpModal')).show();
        }

        function viewDetails(activityData) {
            currentActivityData = activityData;

            const content = document.getElementById('activityDetailsContent');
            const timestamp = new Date(activityData.detected_at).toLocaleString();

            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Basic Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Type:</th>
                                <td><span class="badge bg-secondary">${activityData.type.replace(/_/g, ' ')}</span></td>
                            </tr>
                            <tr>
                                <th>Severity:</th>
                                <td><span class="badge bg-${getSeverityClass(activityData.severity)}">${activityData.severity}</span></td>
                            </tr>
                            <tr>
                                <th>Detected:</th>
                                <td>${timestamp}</td>
                            </tr>
                            <tr>
                                <th>Event ID:</th>
                                <td>${activityData.event_id || 'N/A'}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Network Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>IP Address:</th>
                                <td><code>${activityData.ip_address || 'N/A'}</code></td>
                            </tr>
                            ${activityData.user_agent ? `
                            <tr>
                                <th>User Agent:</th>
                                <td><small><code>${activityData.user_agent}</code></small></td>
                            </tr>
                            ` : ''}
                            ${activityData.device_count ? `
                            <tr>
                                <th>Device Count:</th>
                                <td>${activityData.device_count}</td>
                            </tr>
                            ` : ''}
                            ${activityData.votes_per_minute ? `
                            <tr>
                                <th>Votes/Minute:</th>
                                <td>${activityData.votes_per_minute}</td>
                            </tr>
                            ` : ''}
                        </table>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Security Recommendation:</strong>
                    ${getSecurityRecommendation(activityData.type, activityData.severity)}
                </div>
            `;

            // Show/hide block IP button based on whether we have an IP
            const blockBtn = document.getElementById('blockIpFromModal');
            blockBtn.style.display = activityData.ip_address ? 'block' : 'none';

            new bootstrap.Modal(document.getElementById('activityDetailsModal')).show();
        }

        function blockIpFromDetails() {
            if (currentActivityData && currentActivityData.ip_address) {
                bootstrap.Modal.getInstance(document.getElementById('activityDetailsModal')).hide();
                blockIp(currentActivityData.ip_address);
            }
        }

        function getSeverityClass(severity) {
            return {
                'high': 'danger',
                'medium': 'warning',
                'low': 'info'
            }[severity] || 'secondary';
        }

        function getSecurityRecommendation(type, severity) {
            const recommendations = {
                'multiple_devices_same_ip': 'Multiple devices from the same IP may indicate link sharing or coordinated voting. Consider blocking this IP temporarily.',
                'bot_pattern_detected': 'Automated voting attempt detected. This IP should be blocked immediately to prevent further bot activity.',
                'rapid_voting': 'Unusually fast voting pattern detected. This may indicate automated voting or multiple users sharing a single device.',
                'device_fingerprint_mismatch': 'Device fingerprint changed during voting session. This may indicate session hijacking or device spoofing.'
            };

            return recommendations[type] || 'Suspicious activity detected. Review the details and consider appropriate security measures.';
        }

        async function confirmBlockIp() {
            const form = document.getElementById('blockIpForm');
            const formData = new FormData(form);

            try {
                const response = await fetch('{{ route("admin.security.block-ip") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('blockIpModal')).hide();
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to block IP'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        // Filter functionality
        document.getElementById('severityFilter').addEventListener('change', applyFilters);
        document.getElementById('typeFilter').addEventListener('change', applyFilters);

        function applyFilters() {
            const severityFilter = document.getElementById('severityFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                let show = true;

                if (severityFilter) {
                    const severityBadge = row.querySelector('.badge[class*="bg-"]');
                    if (severityBadge && !severityBadge.textContent.toLowerCase().includes(severityFilter)) {
                        show = false;
                    }
                }

                if (typeFilter && show) {
                    const typeBadge = row.querySelector('.badge.bg-secondary');
                    if (typeBadge && !typeBadge.textContent.toLowerCase().includes(typeFilter.replace(/_/g, ' '))) {
                        show = false;
                    }
                }

                row.style.display = show ? '' : 'none';
            });
        }
    </script>
</x-layouts.app>