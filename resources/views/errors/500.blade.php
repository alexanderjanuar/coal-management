<!-- resources/views/errors/500.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waduh Ada Error - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .floating { animation: float 3s ease-in-out infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .fade-in { animation: fadeIn 1s ease-out; }
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .button-hover {
            transition: all 0.3s ease;
        }
        .button-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-800 via-slate-900 to-gray-900 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <!-- Error Card -->
        <div class="glass-card rounded-2xl p-8 text-center shadow-xl fade-in">
            
            <!-- Icon -->
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center floating border border-red-500/30">
                    <i class="fas fa-exclamation-triangle text-red-400 text-3xl"></i>
                </div>
            </div>
            
            <!-- Error Message -->
            <h1 class="text-3xl font-bold text-white mb-3">
                Wah, Ada Yang Error! 
            </h1>
            <p class="text-gray-300 mb-6 text-base leading-relaxed">
                Maaf nih, sistemnya lagi ngadat. 
                <br>Tim kita udah tau masalahnya dan lagi benerin.
                <br><span class="text-blue-300">Coba refresh halaman atau tunggu sebentar ya! 😊</span>
            </p>
            
            <!-- Error Code -->
            <div class="bg-white/5 rounded-lg p-4 mb-6 border border-white/10">
                <div class="text-red-300 font-mono text-sm font-medium">
                    <i class="fas fa-bug mr-2"></i>
                    Error Code: {{ $status ?? '500' }}
                </div>
                <div class="text-gray-400 text-xs mt-1">Internal Server Error</div>
                @if(isset($errorId))
                <div class="text-gray-500 text-xs mt-2 font-mono">
                    ID: {{ $errorId }}
                </div>
                @endif
            </div>
            
            <!-- Fun Error Messages -->
            <div class="bg-white/5 rounded-lg p-3 mb-6 border border-white/10">
                <div class="text-yellow-300 text-sm">
                    <i class="fas fa-lightbulb mr-2"></i>
                    <span id="funMessage">Server lagi istirahat sebentar...</span>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="space-y-3">
                <button onclick="reloadWithAnimation()" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg button-hover flex items-center justify-center gap-2">
                    <i class="fas fa-redo-alt"></i>
                    <span>Coba Lagi</span>
                </button>
                
                <a href="{{ url('/') }}" 
                   class="w-full bg-white/10 hover:bg-white/20 text-white font-medium py-3 px-6 rounded-lg button-hover flex items-center justify-center gap-2 border border-white/20">
                    <i class="fas fa-home"></i>
                    <span>Ke Beranda</span>
                </a>
                
                @php
                    // Prepare debug message for WhatsApp
                    $debugMessage = "🚨 *ERROR REPORT* 🚨\n\n";
                    $debugMessage .= "📍 *URL:* " . request()->url() . "\n";
                    $debugMessage .= "🔢 *Error Code:* " . ($status ?? '500') . "\n";
                    $debugMessage .= "⏰ *Waktu:* " . now()->format('d M Y, H:i:s') . " WIB\n";
                    $debugMessage .= "🌐 *User Agent:* " . request()->userAgent() . "\n";
                    $debugMessage .= "📱 *IP Address:* " . request()->ip() . "\n\n";
                    
                    if(isset($exception)) {
                        $debugMessage .= "🐛 *DETAIL ERROR:*\n";
                        $debugMessage .= "💬 *Message:* " . $exception->getMessage() . "\n";
                        $debugMessage .= "📂 *File:* " . $exception->getFile() . "\n";
                        $debugMessage .= "📍 *Line:* " . $exception->getLine() . "\n\n";
                        
                        // Get the first few lines of stack trace
                        $trace = $exception->getTraceAsString();
                        $traceLines = explode("\n", $trace);
                        $shortTrace = implode("\n", array_slice($traceLines, 0, 3));
                        $debugMessage .= "📜 *Stack Trace (3 lines):*\n" . $shortTrace . "\n\n";
                    }
                    
                    $debugMessage .= "Tolong dicek ya admin! 🙏";
                    
                    // URL encode the message
                    $whatsappMessage = urlencode($debugMessage);
                @endphp
                
                <a href="https://wa.me/6281253721672?text={{ $whatsappMessage }}" 
                   target="_blank"
                   class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg button-hover flex items-center justify-center gap-2 text-sm">
                    <i class="fab fa-whatsapp"></i>
                    <span>Chat Admin via WhatsApp</span>
                </a>
            </div>
            
            <!-- Debug Info (only in development) -->
            @if(config('app.debug') && isset($exception))
            <div class="mt-6 p-4 bg-red-500/10 border border-red-400/20 rounded-lg text-left">
                <div class="text-red-300 font-medium text-sm mb-2 flex items-center">
                    <i class="fas fa-code mr-2"></i>
                    Debug Info:
                </div>
                <div class="text-xs text-red-200 font-mono break-all space-y-1 bg-black/20 p-3 rounded">
                    <div><strong>Message:</strong> {{ $exception->getMessage() }}</div>
                    <div><strong>File:</strong> {{ $exception->getFile() }}</div>
                    <div><strong>Line:</strong> {{ $exception->getLine() }}</div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6 text-gray-400 text-sm">
            <p>{{ config('app.name') }} &copy; {{ date('Y') }}</p>
            <p class="mt-1 text-gray-500 text-xs">
                {{ now()->format('d M Y, H:i') }}
            </p>
        </div>
    </div>

    <script>
        // Simple error messages
        const funMessages = [
            "Server lagi istirahat sebentar...",
            "Sistemnya lagi maintenance nih",
            "Ada yang error di belakang layar",
            "Lagi diperbaiki sama tim IT",
            "Tunggu sebentar ya, lagi dibenerin",
            "Server lagi reload ulang"
        ];

        // Change message every 4 seconds
        let messageIndex = 0;
        setInterval(() => {
            messageIndex = (messageIndex + 1) % funMessages.length;
            document.getElementById('funMessage').textContent = funMessages[messageIndex];
        }, 4000);

        // Auto-reload after 60 seconds
        setTimeout(() => {
            if (confirm('Udah 1 menit nih, mau dicoba reload? 🤔')) {
                window.location.reload();
            }
        }, 60000);

        // Reload with animation
        function reloadWithAnimation() {
            const button = event.target.closest('button');
            const icon = button.querySelector('i');
            const span = button.querySelector('span');
            
            icon.style.animation = 'spin 0.5s linear infinite';
            button.disabled = true;
            span.textContent = 'Loading...';
            
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }

        // Add spin animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>