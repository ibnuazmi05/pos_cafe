# POS Cafe

Aplikasi Point of Sale (POS) berbasis web untuk operasional cafe. Sistem ini mencakup transaksi kasir, manajemen shift, stok bahan yang berkurang otomatis sesuai resep, pembelian ke supplier, verifikasi transaksi, dan laporan yang dapat diekspor ke PDF dan Excel.

## Fitur

### Kasir
- **Shift kasir**: buka dan tutup shift. Halaman POS hanya bisa dipakai saat ada shift aktif.
- **POS**: pilih menu, atur jumlah, dan total dihitung langsung.
- **Pembayaran** tunai atau QRIS, lengkap dengan perhitungan kembalian.
- **Struk transaksi** dengan nomor invoice otomatis.

### Admin
- **Data master**: kategori, menu, bahan baku, dan supplier.
- **Resep per menu**: setiap penjualan otomatis mengurangi stok bahan sesuai resep.
- **Manajemen stok**: stok masuk, stok keluar, dan stok opname dengan riwayat pergerakan.
- **Purchase order**: alur Draft, Dipesan, Diterima, atau Dibatalkan.
- **Verifikasi transaksi**: transaksi ditandai Terverifikasi atau Ditandai.
- **Persetujuan void**: setujui atau tolak pengajuan kasir. Stok dikembalikan saat void disetujui.
- **Monitoring shift** dengan fitur tutup paksa.

### Superadmin (Pemilik)
- **Dashboard** ringkasan usaha.
- **Manajemen karyawan**: tambah, ubah, serta aktifkan atau nonaktifkan akun.
- **Pengaturan cafe**: nama dan alamat (muncul di struk), PPN, dan diskon tetap dalam persen. Kasir tidak mengetik diskon sendiri.
- **Pembulatan total** otomatis pada setiap transaksi.
- **Laporan** penjualan, pembelian, dan stok, dengan ekspor PDF dan Excel.
- **Log aktivitas** untuk audit tindakan pengguna.

## Peran Pengguna

| Peran | Hak akses |
|-------|-----------|
| Superadmin | Karyawan, pengaturan cafe, laporan, log aktivitas, dan pemantauan transaksi serta pembelian |
| Admin | Data master, stok, purchase order, verifikasi transaksi, void, dan shift |
| Kasir | Shift, transaksi POS, dan pengajuan void |

## Teknologi

- PHP 8.3 dan Laravel 13
- Blade, Alpine.js, dan Tailwind CSS 4 (Vite)
- MySQL 8 atau MariaDB 10.6+
- Laravel Breeze untuk autentikasi
- barryvdh/laravel-dompdf dan Maatwebsite Excel untuk laporan

## Instalasi

Kebutuhan: PHP 8.3+, Composer 2, Node.js 20+, dan MySQL.

```bash
git clone https://github.com/USERNAME/pos-cafe.git
cd pos-cafe

composer install
cp .env.example .env
php artisan key:generate
```

Buat database baru, lalu atur koneksinya di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE= nama_database
DB_USERNAME= database_user
DB_PASSWORD=
```

Jalankan migrasi dengan data contoh, bangun aset, lalu start server:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Buka http://127.0.0.1:8000.

## Akun Demo

Hanya untuk development. Password semua akun adalah `password`.

| Peran | Email |
|-------|-------|
| Superadmin | superadmin@cafe.test |
| Admin | admin@cafe.test |
| Kasir | kasir@cafe.test |

Di production, jangan jalankan `UserSeeder`. Buat akun pemilik dengan:

```bash
php artisan pos:create-superadmin
```

## Pembuat

Ibnu Khairul Azmi