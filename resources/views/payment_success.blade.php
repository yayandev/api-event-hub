<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Hub - Payment Success</title>
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

    <!-- Success Page -->
    <div id="successPage" class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Success Icon -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-gradient-success rounded-full mb-4 animate-bounce-in">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2 animate-slide-up">Pembayaran Berhasil!</h1>
                <p class="text-gray-600 animate-slide-up">Terima kasih telah melakukan pemesanan tiket</p>
            </div>

            <!-- Success Card -->
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-6 animate-slide-up">
                <div class="border-b border-gray-200 pb-4 mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Detail Pesanan</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Order ID</span>
                            <span class="font-medium text-gray-800">{{ $order->order_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Event</span>
                            <span class="font-medium text-gray-800">
                                {{ $order->event->title }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tanggal</span>
                            <span class="font-medium text-gray-800">
                                {{ \Carbon\Carbon::parse($order->event->start_datetime)->format('d-m-Y H:i') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Jumlah Tiket</span>
                            <span class="font-medium text-gray-800">
                                {{ $order->items->sum(function ($item) {
                                    return $item->quantity;
                                }) }}
                                Tiket
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Bayar</span>
                            <span class="font-bold text-green-600">
                                Rp{{ number_format($order->total_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-green-800 font-medium">E-ticket telah dikirim ke email Anda</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3 animate-slide-up">
                <button
                    class="w-full bg-gradient-to-r from-primary to-secondary text-white py-3 px-6 rounded-lg font-medium hover:shadow-lg transform hover:scale-105 transition-all duration-200">
                    Download E-Ticket
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
