<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Laravel App' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    @vite([
        'resources/css/app.css',
        'resources/css/auth.css',
        'resources/css/global.css',
        'resources/css/plugins.css',
        'resources/css/style.css',
        'resources/js/app.js'
    ])
    <style>
        /* Preloader Styles */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .preloader-logo {
            width: 80px;
            height: auto;
            animation: pulse 1.5s ease-in-out infinite;
        }

        .preloader-text {
            margin-top: 20px;
            font-family: 'Roboto', sans-serif;
            color: #f27b33;
            font-size: 18px;
            font-weight: 500;
        }

        .loading-dots {
            display: inline-block;
            margin-left: 5px;
        }

        .loading-dots::after {
            content: '';
            animation: dots 1.5s steps(4, end) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.7;
            }
        }

        @keyframes dots {
            0%, 20% {
                content: '';
            }
            40% {
                content: '.';
            }
            60% {
                content: '..';
            }
            80%, 100% {
                content: '...';
            }
        }
    </style>
</head>
<body>
    <!-- Preloader -->
    <div id="preloader">
        <img src="{{ asset('image/envato-labs-image-edit (6).png') }}" alt="Loading..." class="preloader-logo">
        <div class="preloader-text">
            Loading<span class="loading-dots"></span>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark bg-light shadow-sm py-2 mb-4">
        <div class="container">
            <a class="navbar-brand py-1 d-flex align-items-center text-dark" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}">
                <img src="{{ asset('image/envato-labs-image-edit (6).png') }}" alt="Logo" class="me-2" style="height: 30px; width: auto;">
                <span class="text-dark">Voting System</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                @if(auth()->user()->isAdmin())
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.events.index') }}">
                                <i class="fas fa-calendar-alt me-1"></i>Events
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.events.create') }}">
                                <i class="fas fa-plus me-1"></i>New Event
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.security.dashboard') }}">
                                <i class="fas fa-shield-alt me-1"></i>Security
                            </a>
                        </li>
                    </ul>
                @endif
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            {{ auth()->user()->name }}
                            @if(auth()->user()->isAdmin())
                                <span class="badge bg-danger ms-1">Admin</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu">
                            @if(auth()->user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                                </a></li>
                                @endif
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-cog me-2"></i>Profile Settings
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.activity') }}">
                                <i class="fas fa-history me-2"></i>Activity Log
                            </a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4">
        {{ $slot }}
    </main>

    @livewireScripts

    <script>
        // Hide preloader when page is fully loaded
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                // Add fade out effect
                preloader.style.opacity = '0';
                preloader.style.transition = 'opacity 0.5s ease-out';

                // Remove preloader after fade out
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 500);
            }
        });

        // Fallback: Hide preloader after 3 seconds if page hasn't loaded
        setTimeout(function() {
            const preloader = document.getElementById('preloader');
            if (preloader && preloader.style.display !== 'none') {
                preloader.style.opacity = '0';
                preloader.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 500);
            }
        }, 3000);
    </script>
</body>
</html>