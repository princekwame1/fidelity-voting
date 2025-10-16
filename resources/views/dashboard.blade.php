<x-layouts.app>
    <x-slot name="title">User Dashboard</x-slot>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Welcome, {{ auth()->user()->name }}!</h4>
                </div>
                <div class="card-body">
                    <p class="card-text">You are logged in as a <strong>{{ ucfirst(auth()->user()->role) }}</strong>.</p>
                    <p class="text-muted">This is your user dashboard. You can access your account information and settings from here.</p>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Profile Information</h5>
                                    <p class="card-text">
                                        <strong>Name:</strong> {{ auth()->user()->name }}<br>
                                        <strong>Email:</strong> {{ auth()->user()->email }}<br>
                                        <strong>Role:</strong> {{ ucfirst(auth()->user()->role) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Account Actions</h5>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-primary" type="button">Update Profile</button>
                                        <button class="btn btn-secondary" type="button">Change Password</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>