<div style="padding: 0.5rem 0; color: #374151;" class="dark:text-gray-300">
    <ul style="list-style-type: disc; padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem; margin: 0;">
        <li><strong>🔒 Masking Data Finansial untuk Akun Demo:</strong> Nilai Pendapatan (Dashboard), Harga (Tabel Appointment), Harga Akhir (Form Appointment), dan Harga Dasar (Tabel Layanan) kini ditampilkan sebagai <code>Rp ***</code> khusus untuk role <code>demo_super_admin</code>. Field Harga Akhir pada form juga diganti dengan <em>Placeholder</em> agar tidak bisa dimanipulasi.</li>
        <li><strong>⚡ Optimasi Performa Kalender:</strong> Perbaikan masalah <em>N+1 Query</em> pada widget kalender appointment — jumlah query dikurangi dari ~300 menjadi hanya 3 query melalui <em>eager loading</em> relasi.</li>
        <li><strong>🗃️ Selective Column Loading:</strong> Kalender kini hanya memuat 6 kolom yang dibutuhkan (id, branch_id, patient_id, service_id, status, scheduled_at), bukan seluruh kolom tabel appointments.</li>
        <li><strong>📇 Database Index Optimization:</strong> Penambahan indeks pada kolom <code>scheduled_at</code> dan composite indeks <code>(branch_id, scheduled_at)</code> untuk mempercepat query pencarian jadwal berdasarkan tanggal dan cabang.</li>
    </ul>
</div>
