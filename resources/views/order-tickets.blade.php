<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Hub - Invoice & Print Ticket</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 30px;
            color: #1e293b;
        }

        .invoice-container {
            background: #f8fafc;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            border: 2px solid #e2e8f0;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #1e293b;
        }

        .invoice-number {
            font-size: 14px;
            color: #64748b;
            background: white;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .detail-section h3 {
            font-size: 14px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .detail-section p {
            margin-bottom: 8px;
            color: #1e293b;
        }

        .order-items {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin: 30px 0;
        }

        .order-items table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-items th {
            background: #f1f5f9;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .order-items td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
        }

        .order-items tr:last-child td {
            border-bottom: none;
        }

        .total-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .total-row.final {
            border-top: 2px solid #e2e8f0;
            margin-top: 15px;
            padding-top: 15px;
            font-weight: bold;
            font-size: 18px;
            color: #1e293b;
        }

        .status-badge {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .print-ticket-section {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            margin: 30px 0;
        }

        .print-ticket-section h3 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        .print-ticket-section p {
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .print-ticket-btn {
            background: white;
            color: #6366f1;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .print-ticket-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .important-info {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
        }

        .important-info h3 {
            color: #92400e;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .important-info ul {
            margin-left: 20px;
            color: #92400e;
        }

        .important-info li {
            margin-bottom: 8px;
        }

        .footer {
            background: #1e293b;
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .footer p {
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .social-links {
            margin-top: 20px;
        }

        .social-links a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            opacity: 0.8;
        }

        .social-links a:hover {
            opacity: 1;
        }

        /* Ticket Print Page Styles */
        .ticket-page {
            display: none;
            max-width: 800px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
        }

        .ticket-page.active {
            display: block;
        }

        .ticket-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .ticket-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 20px solid transparent;
            border-right: 20px solid transparent;
            border-top: 20px solid #8b5cf6;
        }

        .ticket-content {
            padding: 40px;
        }

        .ticket-main {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 20px;
            padding: 40px;
            margin: 30px 0;
            position: relative;
            border: 3px dashed #cbd5e1;
        }

        .event-title {
            font-size: 32px;
            font-weight: bold;
            color: #1e293b;
            text-align: center;
            margin-bottom: 10px;
        }

        .event-subtitle {
            font-size: 18px;
            color: #64748b;
            text-align: center;
            margin-bottom: 40px;
        }

        .ticket-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .ticket-info-item {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .ticket-info-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .ticket-info-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
        }

        .qr-section {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .qr-code {
            width: 200px;
            height: 200px;
            background: #f8fafc;
            border: 3px solid #e2e8f0;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 14px;
            color: #64748b;
            text-align: center;
            font-family: monospace;
        }

        .ticket-number {
            font-size: 20px;
            font-weight: bold;
            color: #6366f1;
            margin-bottom: 15px;
        }

        .qr-instruction {
            font-size: 16px;
            color: #64748b;
            margin-top: 20px;
        }

        .ticket-footer {
            background: #1e293b;
            color: white;
            padding: 30px;
            text-align: center;
            margin-top: 40px;
        }

        .back-btn {
            background: #64748b;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin: 20px 10px;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background: #475569;
        }

        .print-btn {
            background: #6366f1;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin: 20px 10px;
            transition: background-color 0.3s;
        }

        .print-btn:hover {
            background: #4f46e5;
        }

        @media (max-width: 600px) {

            .ticket-main {
                padding: 20px;
            }

            .content {
                padding: 10px;
            }

            .invoice-container {
                padding: 10px;
            }

            .invoice-details {
                grid-template-columns: 1fr;
            }

            .header {
                padding: 20px;
            }

            .footer {
                padding: 20px;
            }

            .ticket-details {
                grid-template-columns: 1fr;
            }

            .invoice-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        @media print {
            body {
                background: white;
            }

            .email-container {
                box-shadow: none;
            }

            .back-btn,
            .print-btn {
                display: none;
            }

            .ticket-page {
                margin: 0;
                padding: 0;
            }
        }

        .qr-code-image {
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body>
    <!-- Ticket Print Page -->
    <div id="ticketPage" class="ticket-page active">
        <div class="ticket-header">
            <h1>🎫 Event Hub E-Ticket</h1>
            <p>Tunjukkan tiket ini di pintu masuk</p>
        </div>

        <div class="ticket-content">
            <div class="ticket-main">
                <div class="event-title">{{ $order->event->title }}</div>
                <div class="event-subtitle">{{ $order->event->short_description }}</div>

                <div class="ticket-details">
                    <div class="ticket-info-item">
                        <div class="ticket-info-label">Tanggal & Waktu</div>
                        <div class="ticket-info-value">
                            {{ \Carbon\Carbon::parse($order->event->start_datetime)->format('d-m-Y H:i') }} -
                            <br>
                            {{ \Carbon\Carbon::parse($order->event->end_datetime)->format('d-m-Y H:i') }}
                        </div>
                    </div>

                    <div class="ticket-info-item">
                        <div class="ticket-info-label">Lokasi</div>
                        <div class="ticket-info-value">
                            {{ $order->event->venue }}<br>
                            {{ $order->event->location }}
                        </div>
                    </div>

                    <div class="ticket-info-item">
                        <div class="ticket-info-label">Kategori</div>
                        <div class="ticket-info-value">
                            {{ $tickets->first()->ticketType->name }}
                        </div>
                    </div>

                    <div class="ticket-info-item">
                        <div class="ticket-info-label">Pemegang Tiket</div>
                        <div class="ticket-info-value">
                            {{ $order->user->name }}<br>
                            {{ count($tickets) }} Tiket
                        </div>
                    </div>
                </div>

                @foreach ($tickets as $item => $tiket)
                    <div class="qr-section">
                        <div class="ticket-number">Tiket {{ $tiket->ticket_code }}</div>
                        <div class="qr-code">
                            <img src="{{ $tiket->qr_code }}" alt="QR Code" class="qr-code-image">
                        </div>
                        <div class="qr-instruction">
                            Scan QR code ini di pintu masuk untuk verifikasi tiket
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="important-info">
                <h3>⚠️ Ketentuan Masuk</h3>
                <ul>
                    <li>Harap datang 30 menit sebelum acara dimulai</li>
                    <li>Tunjukkan tiket ini (cetak atau digital) + identitas diri</li>
                    <li>Tiket tidak dapat dipindahtangankan atau direfund</li>
                    <li>Dilarang membawa makanan dan minuman dari luar</li>
                    <li>Tas akan diperiksa di pintu masuk</li>
                    <li>Patuhi protokol kesehatan yang berlaku</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <button class="print-btn" onclick="window.print()">🖨️ Print Tiket</button>
            </div>
        </div>

        <div class="ticket-footer">
            <p><strong>Event Hub</strong> | support@eventhub.com | +62 *******</p>
            <p>Simpan tiket ini dengan baik dan jangan bagikan kepada orang lain</p>
        </div>
    </div>

    <script>
        // Add some interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to buttons
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.style.transition = 'all 0.3s ease';
            });

            // Add click animation to QR code
            const qrCode = document.querySelector('.qr-code');
            if (qrCode) {
                qrCode.addEventListener('click', function() {
                    this.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                });
            }

        });
    </script>
</body>

</html>
