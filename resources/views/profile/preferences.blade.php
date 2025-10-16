<x-layouts.app title="Preferences">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-muted"><i class="fas fa-cog"></i> Preferences</h4>
        <div>
            <a href="{{ route('profile.edit') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>

    @if(session('status') === 'preferences-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> Preferences updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-10">
            <form method="POST" action="{{ route('profile.preferences.update') }}">
                @csrf
                @method('PATCH')

                <!-- Notification Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bell"></i> Notification Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_notifications"
                                           name="email_notifications" value="1"
                                           {{ old('email_notifications', $user->getPreference('email_notifications', true)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_notifications">
                                        <strong>Email Notifications</strong>
                                        <br><small class="text-muted">Receive notifications via email</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="sms_notifications"
                                           name="sms_notifications" value="1"
                                           {{ old('sms_notifications', $user->getPreference('sms_notifications', false)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sms_notifications">
                                        <strong>SMS Notifications</strong>
                                        <br><small class="text-muted">Receive notifications via SMS</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Notification Types:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Event reminders (for admins)</li>
                                <li>Security alerts</li>
                                <li>System updates</li>
                                <li>Account changes</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-shield-alt"></i> Security Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="two_factor_enabled"
                                   name="two_factor_enabled" value="1"
                                   {{ old('two_factor_enabled', $user->getPreference('two_factor_enabled', false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="two_factor_enabled">
                                <strong>Two-Factor Authentication</strong>
                                <br><small class="text-muted">Add an extra layer of security to your account</small>
                            </label>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Note:</strong> Two-factor authentication is currently in development.
                            This setting will be enabled in a future update.
                        </div>
                    </div>
                </div>

                <!-- Regional Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-globe"></i> Regional Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select class="form-select" id="timezone" name="timezone">
                                        <option value="">Select Timezone</option>
                                        <option value="UTC" {{ old('timezone', $user->getPreference('timezone', 'UTC')) === 'UTC' ? 'selected' : '' }}>
                                            UTC (Coordinated Universal Time)
                                        </option>
                                        <option value="America/New_York" {{ old('timezone', $user->getPreference('timezone')) === 'America/New_York' ? 'selected' : '' }}>
                                            Eastern Time (ET)
                                        </option>
                                        <option value="America/Chicago" {{ old('timezone', $user->getPreference('timezone')) === 'America/Chicago' ? 'selected' : '' }}>
                                            Central Time (CT)
                                        </option>
                                        <option value="America/Denver" {{ old('timezone', $user->getPreference('timezone')) === 'America/Denver' ? 'selected' : '' }}>
                                            Mountain Time (MT)
                                        </option>
                                        <option value="America/Los_Angeles" {{ old('timezone', $user->getPreference('timezone')) === 'America/Los_Angeles' ? 'selected' : '' }}>
                                            Pacific Time (PT)
                                        </option>
                                        <option value="Europe/London" {{ old('timezone', $user->getPreference('timezone')) === 'Europe/London' ? 'selected' : '' }}>
                                            London (GMT)
                                        </option>
                                        <option value="Europe/Paris" {{ old('timezone', $user->getPreference('timezone')) === 'Europe/Paris' ? 'selected' : '' }}>
                                            Paris (CET)
                                        </option>
                                        <option value="Asia/Tokyo" {{ old('timezone', $user->getPreference('timezone')) === 'Asia/Tokyo' ? 'selected' : '' }}>
                                            Tokyo (JST)
                                        </option>
                                        <option value="Australia/Sydney" {{ old('timezone', $user->getPreference('timezone')) === 'Australia/Sydney' ? 'selected' : '' }}>
                                            Sydney (AEST)
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="language" class="form-label">Language</label>
                                    <select class="form-select" id="language" name="language">
                                        <option value="en" {{ old('language', $user->getPreference('language', 'en')) === 'en' ? 'selected' : '' }}>
                                            English
                                        </option>
                                        <option value="es" {{ old('language', $user->getPreference('language')) === 'es' ? 'selected' : '' }}>
                                            Español
                                        </option>
                                        <option value="fr" {{ old('language', $user->getPreference('language')) === 'fr' ? 'selected' : '' }}>
                                            Français
                                        </option>
                                        <option value="de" {{ old('language', $user->getPreference('language')) === 'de' ? 'selected' : '' }}>
                                            Deutsch
                                        </option>
                                        <option value="it" {{ old('language', $user->getPreference('language')) === 'it' ? 'selected' : '' }}>
                                            Italiano
                                        </option>
                                        <option value="pt" {{ old('language', $user->getPreference('language')) === 'pt' ? 'selected' : '' }}>
                                            Português
                                        </option>
                                        <option value="ja" {{ old('language', $user->getPreference('language')) === 'ja' ? 'selected' : '' }}>
                                            日本語
                                        </option>
                                        <option value="zh" {{ old('language', $user->getPreference('language')) === 'zh' ? 'selected' : '' }}>
                                            中文
                                        </option>
                                    </select>
                                    <small class="text-muted">Language localization is coming in a future update</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Preferences
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="resetToDefaults()">
                        <i class="fas fa-undo"></i> Reset to Defaults
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <!-- Current Settings Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-list"></i> Current Settings
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Email Notifications:</strong>
                        <span class="badge bg-{{ $user->getPreference('email_notifications', true) ? 'success' : 'secondary' }}">
                            {{ $user->getPreference('email_notifications', true) ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>SMS Notifications:</strong>
                        <span class="badge bg-{{ $user->getPreference('sms_notifications', false) ? 'success' : 'secondary' }}">
                            {{ $user->getPreference('sms_notifications', false) ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Two-Factor Auth:</strong>
                        <span class="badge bg-{{ $user->getPreference('two_factor_enabled', false) ? 'success' : 'secondary' }}">
                            {{ $user->getPreference('two_factor_enabled', false) ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Timezone:</strong><br>
                        <small class="text-muted">{{ $user->getPreference('timezone', 'UTC') }}</small>
                    </div>
                    <div class="mb-2">
                        <strong>Language:</strong><br>
                        <small class="text-muted">{{ strtoupper($user->getPreference('language', 'en')) }}</small>
                    </div>
                </div>
            </div>

            <!-- Tips & Information -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lightbulb"></i> Tips & Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-bell"></i> Notifications</h6>
                        <p class="small mb-0">
                            You can control what notifications you receive. Email notifications include security alerts and important account changes.
                        </p>
                    </div>

                    <div class="alert alert-success">
                        <h6><i class="fas fa-shield-alt"></i> Security</h6>
                        <p class="small mb-0">
                            Enable two-factor authentication for enhanced account security. This adds an extra verification step when logging in.
                        </p>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-clock"></i> Timezone</h6>
                        <p class="small mb-0">
                            Setting your correct timezone ensures all dates and times are displayed accurately for your location.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resetToDefaults() {
            if (confirm('Are you sure you want to reset all preferences to default values?')) {
                // Reset form to default values
                document.getElementById('email_notifications').checked = true;
                document.getElementById('sms_notifications').checked = false;
                document.getElementById('two_factor_enabled').checked = false;
                document.getElementById('timezone').value = 'UTC';
                document.getElementById('language').value = 'en';

                // Show confirmation
                const alert = document.createElement('div');
                alert.className = 'alert alert-info alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-info-circle"></i> Preferences reset to defaults. Don't forget to save your changes!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.row').insertBefore(alert, document.querySelector('.row').firstChild);
            }
        }

        // Show timezone info
        document.getElementById('timezone').addEventListener('change', function(e) {
            const timezone = e.target.value;
            if (timezone) {
                const now = new Date();
                const timeInZone = now.toLocaleString('en-US', { timeZone: timezone });
                console.log(`Current time in ${timezone}: ${timeInZone}`);
            }
        });
    </script>
</x-layouts.app>