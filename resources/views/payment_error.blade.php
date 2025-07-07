<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Hub - Payment Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#8b5cf6',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }

            50% {
                opacity: 1;
                transform: scale(1.05);
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .animate-slide-up {
            animation: slideInUp 0.6s ease-out;
        }

        .animate-bounce-in {
            animation: bounceIn 0.8s ease-out;
        }

        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .bg-gradient-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">


    <!-- Error Page -->
    <div id="errorPage" class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Error Icon -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-gradient-error rounded-full mb-4 animate-shake">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2 animate-slide-up">Pembayaran Gagal!</h1>
                <p class="text-gray-600 animate-slide-up">Maaf, terjadi kesalahan saat memproses pembayaran</p>
            </div>

            <!-- Error Card -->
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-6 animate-slide-up">
                <div class="border-b border-gray-200 pb-4 mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Detail Kesalahan</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Order ID</span>
                            <span class="font-medium text-gray-800">
                                {{ $order->order_number }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Event</span>
                            <span class="font-medium text-gray-800">
                                {{ $order->event->title }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Waktu</span>
                            <span class="font-medium text-gray-800">
                                {{ \Carbon\Carbon::parse($order->event->start_datetime)->format('d-m-Y H:i') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status</span>
                            <span class="font-bold text-red-600">
                                {{ $order->status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-red-800 font-medium mb-1">Alasan Kegagalan:</p>
                            <p class="text-red-700 text-sm">Dana tidak mencukupi atau kartu kredit ditolak oleh bank
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-blue-800 font-medium mb-1">Saran:</p>
                            <p class="text-blue-700 text-sm">Periksa saldo rekening atau hubungi bank untuk informasi
                                lebih lanjut</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3 animate-slide-up">
                <button
                    class="w-full bg-gradient-to-r from-primary to-secondary text-white py-3 px-6 rounded-lg font-medium hover:shadow-lg transform hover:scale-105 transition-all duration-200">
                    Coba Lagi
                </button>
                <button onclick="goHome()"
                    class="w-full text-primary border border-primary py-3 px-6 rounded-lg font-medium hover:bg-primary hover:text-white transition-colors">
                    Kembali ke Beranda
                </button>
            </div>
        </div>
    </div>

    <script>
        function showSuccess() {
            document.getElementById('homePage').classList.add('hidden');
            document.getElementById('errorPage').classList.add('hidden');
            document.getElementById('successPage').classList.remove('hidden');

            // Add animation delay for elements
            setTimeout(() => {
                const elements = document.querySelectorAll('#successPage .animate-slide-up');
                elements.forEach((el, index) => {
                    el.style.animationDelay = `${index * 0.1}s`;
                });
            }, 100);
        }

        function showError() {
            document.getElementById('homePage').classList.add('hidden');
            document.getElementById('successPage').classList.add('hidden');
            document.getElementById('errorPage').classList.remove('hidden');

            // Add animation delay for elements
            setTimeout(() => {
                const elements = document.querySelectorAll('#errorPage .animate-slide-up');
                elements.forEach((el, index) => {
                    el.style.animationDelay = `${index * 0.1}s`;
                });
            }, 100);
        }

        function goHome() {
            document.getElementById('successPage').classList.add('hidden');
            document.getElementById('errorPage').classList.add('hidden');
            document.getElementById('homePage').classList.remove('hidden');
        }

        // Add subtle particle effects
        function createParticles() {
            const particles = [];
            const particleCount = 20;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className =
                    'fixed w-2 h-2 bg-gradient-to-r from-primary to-secondary rounded-full opacity-20 pointer-events-none';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animation = `float ${3 + Math.random() * 4}s infinite linear`;
                document.body.appendChild(particle);
                particles.push(particle);
            }

            return particles;
        }

        // Add floating animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-20px) rotate(180deg); }
            }
        `;
        document.head.appendChild(style);

        // Initialize particles
        createParticles();

        // Add hover effects to buttons
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>

</html>
