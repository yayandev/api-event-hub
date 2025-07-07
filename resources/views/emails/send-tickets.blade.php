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
            .content {
                padding: 20px;
            }

            .invoice-container {
                padding: 20px;
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
    </style>
</head>

<body>
    <!-- Email Invoice Page -->
    <div id="invoicePage" class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🧾 Event Hub</h1>
            <p>Invoice & Payment Confirmation</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                <strong>Halo {{ $data->user->name }}!</strong><br>
                Terima kasih atas pemesanan Anda! Pembayaran telah berhasil dikonfirmasi. Berikut adalah invoice dan
                detail pesanan Anda:
            </div>

            <!-- Invoice Container -->
            <div class="invoice-container">
                <div class="invoice-header">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">
                        <span class="status-badge">{{ $data->status }}</span>
                    </div>
                </div>

                <div class="invoice-details">
                    <div class="detail-section">
                        <h3>Dari:</h3>
                        <p><strong>Event Hub Indonesia</strong></p>
                        <p>contact@eventhub.com</p>
                    </div>

                    <div class="detail-section">
                        <h3>Kepada:</h3>
                        <p><strong>{{ $data->user->name }}</strong></p>
                        <p>
                            {{ $data->user->email }}
                        </p>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Detail Pesanan:</h3>
                    <p><strong>Tanggal Pesanan:</strong>
                        {{ \Carbon\Carbon::parse($data->created_at)->format('d-m-Y') }}
                    </p>
                    <p><strong>Tanggal Pembayaran:</strong>
                        {{ \Carbon\Carbon::parse($data->paid_at)->format('d-m-Y') }}
                    </p>
                    <p><strong>Metode Pembayaran:</strong> {{ $data->payment_method }}</p>
                    <p><strong>Order ID:</strong> {{ $data->order_number }}</p>
                </div>
            </div>

            <!-- Order Items -->
            <div class="order-items">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $data->event->title }}</strong><br>
                                    <small>{{ $item->ticketType->name }}</small>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>Rp {{ number_format($item->ticketType->price, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Total Section -->
            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>Rp {{ number_format($data->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span>Discount:</span>
                    <span>Rp 0</span>
                </div>
                <div class="total-row final">
                    <span>Total Pembayaran:</span>
                    <span>Rp {{ number_format($data->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Print Ticket Section -->
            <div class="print-ticket-section">
                <h3>🎫 Cetak E-Ticket Anda</h3>
                <p>Untuk mendapatkan e-ticket yang akan digunakan saat masuk event, silakan klik tombol di bawah ini:
                </p>
                <a href ="{{ env('APP_URL') }}/order-tikets/{{ $data->order_number }}" class="print-ticket-btn">
                    🖨️ Cetak E-Ticket
                </a>
            </div>

            <!-- Important Information -->
            <div class="important-info">
                <h3>ℹ️ Informasi Penting</h3>
                <ul>
                    <li><strong>E-ticket harus dicetak atau disimpan di mobile</strong> untuk ditunjukkan saat masuk
                    </li>
                    <li>Pembayaran telah berhasil dikonfirmasi, tidak diperlukan tindakan lebih lanjut</li>
                    <li>Invoice ini adalah bukti pembelian yang sah</li>
                    <li>Simpan email ini sebagai bukti transaksi</li>
                    <li>Hubungi customer service jika ada pertanyaan</li>
                </ul>
            </div>

            <div style="margin-top: 30px; padding: 20px; background: #f8fafc; border-radius: 12px; text-align: center;">
                <p style="color: #64748b; font-size: 14px;">
                    <strong>Order ID:</strong> {{ $data->order_number }} |
                    <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($data->created_at)->format('d-m-Y') }}
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Terima kasih atas kepercayaan Anda!</strong></p>
            <p>Tim Event Hub</p>

            <div class="social-links">
                <a href="#">📧 support@eventhub.co.id</a>
                <a href="#">📞 +62 *********</a>
                <a href="#">📱 +62 *********</a>
            </div>

            <p style="margin-top: 20px; font-size: 12px; opacity: 0.6;">
                © 2024 Event Hub. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>
