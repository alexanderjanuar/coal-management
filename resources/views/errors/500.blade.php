<!-- resources/views/errors/500.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Error Card -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 text-center border border-white/20 shadow-2xl">
            <!-- Icon -->
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-400 text-3xl"></i>
                </div>
            </div>
            
            <!-- Error Message -->
            <h1 class="text-3xl font-bold text-white mb-2">Oops! Something went wrong</h1>
            <p class="text-gray-300 mb-6">We're experiencing some technical difficulties. Our team has been notified and is working to fix this issue.</p>
            
            <!-- Error Code -->
            <div class="bg-white/5 rounded-lg p-4 mb-6 border border-white/10">
                <div class="text-red-400 font-mono text-sm">Error Code: 500</div>
                <div class="text-gray-400 text-xs mt-1">Internal Server Error</div>
            </div>
            
            <!-- Actions -->
            <div class="space-y-3">
                <button onclick="window.location.reload()" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                    <i class="fas fa-sync-alt"></i>
                    Try Again
                </button>
                
                <a href="{{ url('/') }}" 
                   class="w-full bg-white/10 hover:bg-white/20 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 border border-white/20">
                    <i class="fas fa-home"></i>
                    Go to Homepage
                </a>
            </div>
            
            <!-- Debug Info (only in development) -->
            @if(config('app.debug') && isset($exception))
            <div class="mt-6 p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-left">
                <div class="text-red-400 font-semibold text-sm mb-2">Debug Information:</div>
                <div class="text-xs text-gray-300 font-mono break-all">
                    <strong>Message:</strong> {{ $exception->getMessage() }}<br>
                    <strong>File:</strong> {{ $exception->getFile() }}<br>
                    <strong>Line:</strong> {{ $exception->getLine() }}
                </div>
            </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6 text-gray-400 text-sm">
            <p>{{ config('app.name') }} &copy; {{ date('Y') }}</p>
            <p class="mt-1">Error ID: {{ Str::random(8) }} | {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>

    <script>
        // Auto-reload after 30 seconds
        setTimeout(function() {
            if (confirm('Would you like to try reloading the page?')) {
                window.location.reload();
            }
        }, 30000);
        
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate the error icon
            const icon = document.querySelector('.fas.fa-exclamation-triangle');
            if (icon) {
                setInterval(() => {
                    icon.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        icon.style.transform = 'scale(1)';
                    }, 200);
                }, 3000);
            }
        });
    </script>
</body>
</html>