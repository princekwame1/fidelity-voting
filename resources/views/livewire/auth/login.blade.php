<div class="py-3 px-4 " >
    <div class="text-center mb-4">
        <img src="{{ asset('image/envato-labs-image-edit (6).png') }}" alt="Logo" class="mb-3 mx-auto d-block" style="height: 60px; width: auto;">
        {{-- <h3>Login</h3> --}}
        <p class="text-muted">Sign in to your account</p>
    </div>

    <form wire:submit.prevent="login">
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" wire:model="email" placeholder="Enter your email address">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="position-relative">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" wire:model="password" placeholder="Enter your password">
                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" style="text-decoration: none; color: #6c757d;" onclick="togglePassword('password')">
                    <i class="fas fa-eye" id="password-toggle-icon"></i>
                </button>
                @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" wire:model="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                Sign In
            </button>
        </div>
    </form>

    <div class="text-center mt-4">
        <p class="text-muted">Don't have an account? <a href="{{ route('register') }}" wire:navigate>Register</a></p>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(fieldId + '-toggle-icon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</div>
