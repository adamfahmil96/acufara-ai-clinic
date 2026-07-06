<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fonntee Alert</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .alert-box {
            background-color: #fef2f2;
            border: 1px solid #ef4444;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .alert-title {
            color: #dc2626;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-table td:first-child {
            font-weight: 600;
            width: 140px;
            color: #6b7280;
        }
        .action-box {
            background-color: #f0f9ff;
            border: 1px solid #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .action-title {
            color: #2563eb;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .action-list {
            margin: 0;
            padding-left: 20px;
        }
        .action-list li {
            margin-bottom: 8px;
        }
        .footer {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="alert-box">
        <div class="alert-title">⚠️ Fonntee WhatsApp Gateway Terputus</div>
        <p>Sistem monitoring mendeteksi bahwa koneksi Fonntee ke WhatsApp telah terputus. Notifikasi WhatsApp <strong>tidak akan terkirim</strong> sampai koneksi dipulihkan.</p>
    </div>

    <table class="info-table">
        <tr>
            <td>Status</td>
            <td><strong style="color: #dc2626;">Terputus (Disconnected)</strong></td>
        </tr>
        <tr>
            <td>Pesan Error</td>
            <td>{{ $fonnteMessage }}</td>
        </tr>
        <tr>
            <td>Waktu Deteksi</td>
            <td>{{ \Carbon\Carbon::parse($fonnteCheckedAt)->translatedFormat('l, d F Y H:i:s') }} WIB</td>
        </tr>
    </table>

    <div class="action-box">
        <div class="action-title">🔧 Langkah Perbaikan</div>
        <ol class="action-list">
            <li>Buka <strong>Fonntee Dashboard</strong> di <a href="https://fonntee.com">fonntee.com</a></li>
            <li>Login ke akun Fonntee Anda</li>
            <li>Cek status device WhatsApp</li>
            <li>Klik tombol <strong>"Connect"</strong> atau <strong>"Reconnect"</strong></li>
            <li>Scan QR Code dengan WhatsApp di HP Anda</li>
            <li>Pastikan status berubah menjadi <strong>"Connected"</strong></li>
        </ol>
    </div>

    <p><strong>Catatan:</strong> Email ini dikirim secara otomatis oleh sistem monitoring Acufara. Jika Anda sudah memperbaiki koneksi, sistem akan mendeteksi status "Connected" pada pengecekan berikutnya (setiap 5 menit).</p>

    <div class="footer">
        <p>Ini adalah email otomatis dari sistem monitoring Acufara AI Clinic.</p>
        <p>App URL: {{ $fonnteAppUrl }}</p>
    </div>
</body>
</html>
