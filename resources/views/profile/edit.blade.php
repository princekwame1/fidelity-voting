<x-layouts.app title="Profile Settings">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-muted"><i class="fas fa-user-cog"></i> Profile Settings</h4>
        <div>
            <a href="{{ route('profile.activity') }}" class="btn btn-info">
                <i class="fas fa-history"></i> Activity Log
            </a>
            <a href="{{ route('profile.preferences') }}" class="btn btn-secondary">
                <i class="fas fa-cog"></i> Preferences
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            @switch(session('status'))
                @case('profile-updated')
                    <i class="fas fa-check-circle"></i> Profile updated successfully!
                    @break
                @case('password-updated')
                    <i class="fas fa-shield-alt"></i> Password updated successfully!
                    @break
                @case('avatar-removed')
                    <i class="fas fa-trash"></i> Avatar removed successfully!
                    @break
                @default
                    <i class="fas fa-info-circle"></i> {{ session('status') }}
            @endswitch
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-10">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user"></i> Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <!-- Avatar Section -->
                        <div class="mb-4">
                            <label class="form-label">Profile Picture</label>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}"
                                             alt="Avatar"
                                             class="rounded-circle"
                                             width="80" height="80"
                                             style="object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 80px; height: 80px;">
                                            <i class="fas fa-user fa-2x text-white"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                           id="avatar" name="avatar" accept="image/*">
                                    <small class="text-muted">JPG, PNG, GIF up to 2MB</small>
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if($user->avatar)
                                    <div class="ms-2">
                                        <form method="POST" action="{{ route('profile.avatar.remove') }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Remove avatar?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Enter your full name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Enter your email address" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($user->email_verified_at)
                                        <small class="text-success">
                                            <i class="fas fa-check-circle"></i> Verified
                                        </small>
                                    @else
                                        <small class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Not verified
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="Enter your phone number">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" readonly>
                                    <small class="text-muted">Contact administrator to change your role</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror"
                                      id="bio" name="bio" rows="3"
                                      placeholder="Tell us about yourself...">{{ old('bio', $user->bio ?? '') }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum 500 characters</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                               class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password & Security -->
        <div class="col-lg-4">
            <!-- Change Password -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lock"></i> Change Password
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" placeholder="Enter your current password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" placeholder="Enter new password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" required>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-key"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Account Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Account Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Member Since:</strong><br>
                        <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                    </div>
                    <div class="mb-2">
                        <strong>Last Updated:</strong><br>
                        <small class="text-muted">{{ $user->updated_at->format('M d, Y g:i A') }}</small>
                    </div>
                    <div class="mb-2">
                        <strong>Account Type:</strong><br>
                        <span class="badge bg-{{ $user->isAdmin() ? 'danger' : 'primary' }}">
                            {{ $user->isAdmin() ? 'Administrator' : 'User' }}
                        </span>
                    </div>
                    @if($user->isAdmin())
                        <div class="mb-2">
                            <strong>Events Created:</strong><br>
                            <small class="text-muted">{{ $user->events()->count() ?? 0 }} events</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Danger Zone
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Once you delete your account, all of your data will be permanently deleted.
                    </p>
                    <button type="button" class="btn btn-danger w-100"
                            data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="fas fa-trash"></i> Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Delete Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Warning!</strong> This action cannot be undone. All your data will be permanently deleted.
                    </div>
                    <form method="POST" action="{{ route('profile.destroy') }}" id="deleteAccountForm">
                        @csrf
                        @method('DELETE')

                        <div class="mb-3">
                            <label for="delete_password" class="form-label">
                                Confirm your password to delete your account:
                            </label>
                            <input type="password" class="form-control"
                                   id="delete_password" name="password"
                                   placeholder="Enter your current password" required>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                            <label class="form-check-label" for="confirmDelete">
                                I understand that this action cannot be undone
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="deleteAccountForm" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete My Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Avatar preview
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.querySelector('img[alt="Avatar"]') ||
                                document.querySelector('.bg-secondary.rounded-circle');
                    if (img.tagName === 'IMG') {
                        img.src = e.target.result;
                    } else {
                        // Replace placeholder with image
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.alt = 'Avatar';
                        newImg.className = 'rounded-circle';
                        newImg.style.width = '80px';
                        newImg.style.height = '80px';
                        newImg.style.objectFit = 'cover';
                        img.parentNode.replaceChild(newImg, img);
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strength = calculatePasswordStrength(password);

            // You can add a password strength indicator here
            console.log('Password strength:', strength);
        });

        function calculatePasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            return strength;
        }
    </script>
</x-layouts.app>