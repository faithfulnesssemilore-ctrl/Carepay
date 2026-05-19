<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'CarePay' }}</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
            integrity="sha512-pnKJYqw+4KRFz3LrL0rwO6+Hh2Z07W8uT+Zk4l3e5rT+q6b0XuwmZ4Kk3T7xma6p4eXirHJf9p8rvpqk2aEzqQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/css/bootstrap.css', 'resources/css/custom.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @livewireStyles
</head>
<body style="background: radial-gradient(circle at top, rgba(168, 85, 247, 0.25), transparent 24%), linear-gradient(180deg, #090a10 0%, #05060d 100%); min-height:100vh; color:white;">
    <div class="d-flex align-items-center justify-content-center min-vh-100 py-5 px-3">
        <div class="w-100" style="max-width: 540px;">
            <div class="text-center mb-5">
                <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none mb-3">
                    <div class="icon-container gradient-bg-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:16px;">
                        <x-lucide-wallet style="width:24px;height:24px;color:white;" />
                    </div>
                    <span class="gradient-text fw-bold fs-4">CarePay</span>
                </a>
                @if(isset($subtitle))
                    <p class="text-muted-custom mb-0">{{ $subtitle }}</p>
                @endif
            </div>

            {{ $slot }}
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
