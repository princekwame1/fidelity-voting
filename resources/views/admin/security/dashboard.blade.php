<x-layouts.app title="Security Dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-shield-alt"></i> Security Dashboard</h1>
        <div>
            <a href="{{ route('admin.security.suspicious') }}" class="btn btn-warning">
                <i class="fas fa-exclamation-triangle"></i> Suspicious Activity
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Security Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Sessions</h5>
                            <h2 class="card-text">{{ $securityStats['total_sessions'] }}</h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Unique Devices</h5>
                            <h2 class="card-text">{{ $securityStats['unique_devices'] }}</h2>
                        </div>
                        <i class="fas fa-mobile-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Suspicious IPs</h5>
                            <h2 class="card-text">{{ $securityStats['suspicious_ips'] }}</h2>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Blocked IPs</h5>
                            <h2 class="card-text">{{ $securityStats['blocked_ips'] }}</h2>
                        </div>
                        <i class="fas fa-ban fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Events Security Overview -->
        <div class="col-lg-10">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check"></i> Events Security Overview
                    </h5>
                </div>
                <div class="card-body">
                    @if($events->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Sessions</th>
                                        <th>Unique IPs</th>
                                        <th>Risk Level</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($events as $event)
                                        @php
                                            $sessions = $event->votingSessions;
                                            $uniqueIps = $sessions->unique('ip_address')->count();
                                            $ipCounts = $sessions->countBy('ip_address');
                                            $maxFromSingleIp = $ipCounts->max() ?: 0;

                                            $riskLevel = 'low';
                                            $riskClass = 'success';
                                            if ($maxFromSingleIp > 5) {
                                                $riskLevel = 'high';
                                                $riskClass = 'danger';
                                            } elseif ($maxFromSingleIp > 3) {
                                                $riskLevel = 'medium';
                                                $riskClass = 'warning';
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $event->name }}</strong>
                                                @if($event->isActive())
                                                    <span class="badge bg-success ms-1">Active</span>
                                                @endif
                                            </td>
                                            <td>{{ $sessions->count() }}</td>
                                            <td>{{ $uniqueIps }}</td>
                                            <td>
                                                <span class="badge bg-{{ $riskClass }}">
                                                    {{ ucfirst($riskLevel) }}
                                                    @if($maxFromSingleIp > 3)
                                                        ({{ $maxFromSingleIp }} from 1 IP)
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.security.event', $event) }}"
                                                       class="btn btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.events.show', $event) }}"
                                                       class="btn btn-secondary">
                                                        <i class="fas fa-cog"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5>No Recent Events</h5>
                            <p class="text-muted">No events found in the last 30 days.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Security Statistics -->
        <div class="col-lg-4">
            <!-- Device Reuse -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-sync"></i> Device Reuse Rate
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="display-6 text-info">{{ $securityStats['device_reuse_rate'] }}%</div>
                        <small class="text-muted">
                            Indicates potential link sharing
                        </small>
                    </div>
                    <div class="progress mt-3">
                        <div class="progress-bar bg-info" style="width: {{ min($securityStats['device_reuse_rate'], 100) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Top Suspicious IPs -->
            @if(count($securityStats['top_suspicious_ips']) > 0)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle text-warning"></i> Top Suspicious IPs
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($securityStats['top_suspicious_ips'] as $ip => $count)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <code>{{ $ip }}</code>
                                    <small class="text-muted d-block">{{ $count }} sessions</small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-danger btn-sm"
                                            onclick="blockIp('{{ $ip }}')">
                                        <i class="fas fa-ban"></i> Block
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Blocked IPs -->
            @if(count($securityStats['blocked_ips_list']) > 0)
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-ban text-danger"></i> Blocked IPs
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($securityStats['blocked_ips_list'] as $ip => $data)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <code>{{ $ip }}</code>
                                    <small class="text-muted d-block">{{ $data['reason'] ?? 'No reason' }}</small>
                                </div>
                                <button class="btn btn-success btn-sm"
                                        onclick="unblockIp('{{ $ip }}')">
                                    <i class="fas fa-unlock"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
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
                                <option value="Manual review required">Manual review required</option>
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
        function blockIp(ip) {
            document.getElementById('blockIpAddress').value = ip;
            document.getElementById('displayIpAddress').textContent = ip;
            new bootstrap.Modal(document.getElementById('blockIpModal')).show();
        }

        async function confirmBlockIp() {
            const form = document.getElementById('blockIpForm');
            const formData = new FormData(form);

            try {
                const response = await fetch('{{ route("admin.security.block-ip") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
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

        async function unblockIp(ip) {
            if (!confirm(`Are you sure you want to unblock ${ip}?`)) {
                return;
            }

            try {
                const response = await fetch('{{ route("admin.security.unblock-ip") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ip: ip })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to unblock IP'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
    </script>
</x-layouts.app>