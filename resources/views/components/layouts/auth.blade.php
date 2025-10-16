<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Laravel Auth' }}</title>
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
<body class="bg-light">
    <!-- Preloader -->
    <div id="preloader">
        <img src="{{ asset('image/envato-labs-image-edit (6).png') }}" alt="Loading..." class="preloader-logo">
        <div class="preloader-text">
            Loading<span class="loading-dots"></span>
        </div>
    </div>

    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <div class="col-11 col-sm-8 col-md-6 col-lg-4 mx-auto">
                <div class="card shadow">
                    <div class="card-body">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
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