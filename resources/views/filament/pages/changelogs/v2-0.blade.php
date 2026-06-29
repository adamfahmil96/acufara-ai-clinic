<div style="padding: 0.5rem 0; color: #374151;" class="dark:text-gray-300">
    <ul style="list-style-type: disc; padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem; margin: 0;">
        <li><strong>📋 Riwayat Medis pada Form Booking:</strong> Penambahan field <code>medical_history</code> (Riwayat Penyakit) dan <code>allergy_history</code> (Riwayat Alergi) pada tabel appointments. Kedua field bersifat opsional dan tersedia di form booking publik, self-register, serta panel admin.</li>
        <li><strong>📱 Notifikasi WhatsApp Lengkap:</strong> Pesan notifikasi booking baru kini menyertakan informasi riwayat penyakit dan alergi pasien, memudahkan terapis mempersiapkan penanganan sebelum kunjungan.</li>
        <li><strong>🗄️ Migration Baru:</strong> Penambahan kolom <code>medical_history</code> (text, nullable) dan <code>allergy_history</code> (text, nullable) ke tabel <code>appointments</code> — backward compatible, tidak memengaruhi data existing.</li>
    </ul>
</div>
