<div style="padding: 0.5rem 0; color: #374151;" class="dark:text-gray-300">
    <ul style="list-style-type: disc; padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem; margin: 0;">
        <li><strong>📊 Halaman Analitik Baru:</strong> Penambahan menu "Analitik" khusus untuk <code>super_admin</code> dan <code>developer</code> yang menampilkan ringkasan data klinik per bulan — total janji temu, selesai, pendapatan, dan pasien baru.</li>
        <li><strong>🔍 Filter Periode Interaktif:</strong> Admin dapat memfilter data berdasarkan bulan, tahun (input bebas), dan cabang. Data diperbarui secara eksplisit melalui tombol "Terapkan" dengan indikator loading.</li>
        <li><strong>📈 Tren Pendapatan 6 Bulan:</strong> Visualisasi bar chart dengan warna berbeda per bulan untuk membandingkan pendapatan dari jadwal yang sudah selesai, dilengkapi tooltip interaktif.</li>
        <li><strong>📋 Breakdown per Layanan:</strong> Tabel rincian jumlah janji temu dan pendapatan berdasarkan jenis layanan (Akupunktur, Bekam, Baby Spa, dll) pada periode yang dipilih.</li>
        <li><strong>⚡ Optimasi Query:</strong> Pengurangan jumlah query database dari 12 menjadi 5 melalui penggunaan conditional aggregates dan <code>GROUP BY</code> — menghilangkan masalah N+1 pada data tren dan statistik.</li>
        <li><strong>🔒 Akses Terbatas:</strong> Halaman analitik hanya dapat diakses oleh role <code>super_admin</code> dan <code>developer</code>. Data finansial tetap disamarkan (<code>Rp ***</code>) untuk akun demo.</li>
    </ul>
</div>
