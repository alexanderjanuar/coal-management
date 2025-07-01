<!-- resources/views/errors/419.blade.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesi Berakhir - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .fade-in {
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
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

<body
    class="bg-gradient-to-br from-slate-800 via-slate-900 to-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="glass-card rounded-2xl p-8 text-center shadow-xl fade-in">
            <div class="mb-6">
                <div
                    class="mx-auto w-20 h-20 bg-yellow-500/20 rounded-full flex items-center justify-center floating border border-yellow-500/30">
                    <i class="fas fa-clock text-yellow-400 text-3xl"></i>
                </div>
            </div>

            <h1 class="text-3xl font-bold text-white mb-3">Sesi Berakhir</h1>
            <p class="text-gray-300 mb-6 text-base leading-relaxed">
                Sesi kamu udah expired nih.
                <br>Refresh halaman atau coba submit form lagi ya!
                <br><span class="text-yellow-300">Biasanya karena kelamaan di halaman yang sama 😅</span>
            </p>

            <div class="bg-white/5 rounded-lg p-4 mb-6 border border-white/10">
                <div class="text-yellow-300 font-mono text-sm font-medium">
                    <i class="fas fa-hourglass-end mr-2"></i>
                    Error Code: 419
                </div>
                <div class="text-gray-400 text-xs mt-1">Page Expired</div>
            </div>

            <div class="space-y-3">
                <button onclick="window.location.reload()"
                    class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-3 px-6 rounded-lg button-hover flex items-center justify-center gap-2">
                    <i class="fas fa-sync-alt"></i>
                    <span>Refresh Halaman</span>
                </button>

                <a href="{{ url('/') }}"
                    class="w-full bg-white/10 hover:bg-white/20 text-white font-medium py-3 px-6 rounded-lg button-hover flex items-center justify-center gap-2 border border-white/20">
                    <i class="fas fa-home"></i>
                    <span>Ke Beranda</span>
                </a>
            </div>
        </div>

        <div class="text-center mt-6 text-gray-400 text-sm">
            <p>{{ config('app.name') }} &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>

</html>