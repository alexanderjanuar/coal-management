<!-- resources/views/errors/404.blade.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Hilang! - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .floating {
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            25% {
                transform: translateY(-8px) rotate(2deg);
            }

            50% {
                transform: translateY(-15px) rotate(0deg);
            }

            75% {
                transform: translateY(-8px) rotate(-2deg);
            }
        }

        .wiggle {
            animation: wiggle 2s ease-in-out infinite;
        }

        @keyframes wiggle {

            0%,
            100% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(3deg);
            }

            75% {
                transform: rotate(-3deg);
            }
        }

        .gradient-text {
            background: linear-gradient(45deg, #3b82f6, #8b5cf6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .bounce-in {
            animation: bounceIn 1.2s ease-out;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3) translateY(100px);
                opacity: 0;
            }

            50% {
                transform: scale(1.1) translateY(-20px);
            }

            70% {
                transform: scale(0.9) translateY(10px);
            }

            100% {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-blue-400 via-purple-500 to-cyan-400 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Background decorations -->
    <div class="absolute inset-0 overflow-hidden">
        <div
            class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full mix-blend-overlay filter blur-xl opacity-20 floating">
        </div>
        <div class="absolute bottom-20 right-20 w-48 h-48 bg-yellow-300 rounded-full mix-blend-overlay filter blur-xl opacity-20 floating"
            style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/4 w-24 h-24 bg-pink-300 rounded-full mix-blend-overlay filter blur-xl opacity-20 floating"
            style="animation-delay: 2s;"></div>
    </div>

    <div class="max-w-lg w-full relative z-10">
        <!-- Error Card -->
        <div class="glass-effect rounded-3xl p-8 text-center border border-white/30 shadow-2xl bounce-in">

            <!-- Icon -->
            <div class="mb-6">
                <div
                    class="mx-auto w-28 h-28 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center floating shadow-xl">
                    <i class="fas fa-search text-white text-5xl wiggle"></i>
                </div>
            </div>

            <!-- Error Message -->
            <h1 class="text-5xl font-bold text-white mb-3">
                <span class="gradient-text">404</span>
            </h1>
            <h2 class="text-2xl font-bold text-white mb-4">
                Aduh, Halamannya Ilang! 🕵️‍♀️
            </h2>
            <p class="text-white/90 mb-6 text-lg leading-relaxed">
                Kayaknya halaman yang kamu cari itu udah pindah rumah,
                <br>atau mungkin lagi main petak umpet sama kita.
                <br><span class="text-cyan-200">Coba cek lagi linknya, atau balik ke beranda aja! 😊</span>
            </p>

            <!-- Fun Search Box -->
            <div class="glass-effect rounded-2xl p-5 mb-6 border border-white/20">
                <div class="text-blue-200 text-lg font-semibold mb-2">
                    <i class="fas fa-map-marked-alt mr-2"></i>
                    Halaman Tidak Ditemukan
                </div>
                <div class="text-white/70 text-sm">
                    URL: <span class="font-mono text-cyan-200">{{ request()->url() }}</span>
                </div>
            </div>

            <!-- Fun Messages -->
            <div class="bg-white/10 rounded-2xl p-4 mb-6 border border-white/20">
                <div class="text-yellow-200 text-sm">
                    <i class="fas fa-lightbulb mr-2"></i>
                    <span id="funMessage">Mungkin halamannya lagi ngumpet di balik sofa...</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-4">
                <button onclick="goBack()"
                    class="w-full bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 flex items-center justify-center gap-3 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-arrow-left text-lg"></i>
                    <span class="text-lg">Balik ke Sebelumnya</span>
                </button>

                <a href="{{ url('/') }}"
                    class="w-full bg-white/20 hover:bg-white/30 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 flex items-center justify-center gap-3 border border-white/30 hover:border-white/50 transform hover:scale-105">
                    <i class="fas fa-home text-lg"></i>
                    <span class="text-lg">Ke Beranda Aja</span>
                </a>

                <button onclick="searchAgain()"
                    class="w-full bg-white/10 hover:bg-white/20 text-white/90 font-medium py-3 px-6 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 border border-white/20 text-sm">
                    <i class="fas fa-search"></i>
                    <span>Coba Cari Lagi</span>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-white/80 text-sm">
            <p class="flex items-center justify-center gap-2">
                <i class="fas fa-compass text-cyan-300"></i>
                {{ config('app.name') }} &copy; {{ date('Y') }}
                <i class="fas fa-compass text-cyan-300"></i>
            </p>
            <p class="mt-2 text-white/60 text-xs">
                Jangan khawatir, kita bakal bantuin kamu nemuin jalan! 🗺️
            </p>
        </div>
    </div>

    <script>
        // Fun error messages for 404
        const funMessages = [
            "Mungkin halamannya lagi ngumpet di balik sofa... 🛋️",
            "Kayaknya halaman ini lagi jalan-jalan ke dimensi lain 🌌",
            "404 = Halaman lagi main petak umpet sama kamu 👻",
            "Mungkin halamannya lagi makan di kantin server 🍽️",
            "Sepertinya ada yang salah ketik URL-nya nih 🤷‍♀️",
            "Halaman ini mungkin lagi liburan ke Bali 🏝️",
            "404: Not Found = Hilang kayak kaus kaki di mesin cuci 🧦",
            "Mungkin link-nya udah expired kayak susu kemaren 🥛"
        ];

        // Change fun message every 4 seconds
        let messageIndex = 0;
        setInterval(() => {
            messageIndex = (messageIndex + 1) % funMessages.length;
            document.getElementById('funMessage').textContent = funMessages[messageIndex];
        }, 4000);

        // Go back function
        function goBack() {
            const button = event.target.closest('button');
            const icon = button.querySelector('i');
            
            icon.style.animation = 'wiggle 0.5s ease-in-out';
            button.disabled = true;
            button.querySelector('span').textContent = 'Tunggu sebentar...';
            
            setTimeout(() => {
                if (document.referrer && document.referrer !== window.location.href) {
                    window.history.back();
                } else {
                    window.location.href = '{{ url('/') }}';
                }
            }, 800);
        }

        // Search again function  
        function searchAgain() {
            const currentUrl = window.location.pathname;
            const searchQuery = prompt('Mau cari apa nih? 🔍');
            
            if (searchQuery && searchQuery.trim() !== '') {
                // You can customize this to redirect to your search page
                window.location.href = `{{ url('/') }}?search=${encodeURIComponent(searchQuery)}`;
            }
        }

        // Add interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate the search icon on click
            const searchIcon = document.querySelector('.fa-search');
            if (searchIcon) {
                searchIcon.addEventListener('click', () => {
                    searchIcon.style.animation = 'spin 1s linear';
                    setTimeout(() => {
                        searchIcon.style.animation = 'wiggle 2s ease-in-out infinite';
                    }, 1000);
                });
            }

            // Add hover effects to buttons
            const buttons = document.querySelectorAll('button, a');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', () => {
                    button.style.transform = 'scale(1.05) translateY(-3px)';
                    button.style.boxShadow = '0 20px 40px rgba(0,0,0,0.3)';
                });
                button.addEventListener('mouseleave', () => {
                    button.style.transform = 'scale(1) translateY(0)';
                    button.style.boxShadow = '0 10px 30px rgba(0,0,0,0.2)';
                });
            });

            // Add some random sparkles
            createSparkles();
        });

        // Create sparkle effects
        function createSparkles() {
            for (let i = 0; i < 6; i++) {
                setTimeout(() => {
                    const sparkle = document.createElement('div');
                    sparkle.innerHTML = '✨';
                    sparkle.style.position = 'absolute';
                    sparkle.style.left = Math.random() * window.innerWidth + 'px';
                    sparkle.style.top = Math.random() * window.innerHeight + 'px';
                    sparkle.style.fontSize = Math.random() * 20 + 10 + 'px';
                    sparkle.style.pointerEvents = 'none';
                    sparkle.style.animation = 'float 3s ease-in-out forwards';
                    sparkle.style.opacity = '0.7';
                    
                    document.body.appendChild(sparkle);
                    
                    setTimeout(() => {
                        sparkle.remove();
                    }, 3000);
                }, i * 500);
            }
        }
    </script>
</body>

</html>