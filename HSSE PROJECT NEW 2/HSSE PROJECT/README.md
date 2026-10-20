# Hsse Manajemen — Project Manajemen Geodipa Unit Dieng 2

Aplikasi Sistem Informasi Manajemen Keselamatan, Kesehatan Kerja, Keamanan, dan Lindungan Lingkungan (**Health, Safety, Security, and Environment - HSSE**) berbasis web yang dikembangkan khusus untuk **PT Geo Dipa Energi (Persero) Unit Dieng 2**.

Aplikasi ini dibangun menggunakan arsitektur modern berbasis **Laravel 12**, **Blade Templating**, **Bootstrap 5**, **Alpine.js**, **Chart.js**, dan basis data relasional **MySQL**.

---

## DAFTAR ISI

1. [Informasi Umum & Arsitektur](#1-informasi-umum--arsitektur)
2. [Panduan Instalasi & Konfigurasi](#2-panduan-instalasi--konfigurasi)
3. [Dokumentasi Modul & Fitur](#3-dokumentasi-modul--fitur)
   - [3.1 Dashboard Utama](#31-dashboard-utama)
   - [3.2 Modul Inventory](#32-modul-inventory)
   - [3.3 Modul Incident (Kecelakaan Kerja)](#33-modul-incident-kecelakaan-kerja)
   - [3.4 Modul Daily Work](#34-modul-daily-work)
   - [3.5 Modul HSSE Plan](#35-modul-hsse-plan)
   - [3.6 Modul Events](#36-modul-events)
   - [3.7 Modul Workforce & Man Hours](#37-modul-workforce--man-hours)
   - [3.8 Modul Issue & Follow Up](#38-modul-issue--follow-up)
   - [3.9 Modul Gasdetektor](#39-modul-gasdetektor)
   - [3.10 Modul Manajemen Pengguna & Audit Log](#310-modul-manajemen-pengguna--audit-log)
4. [Standardisasi Media & Upload Gambar](#4-standardisasi-media--upload-gambar)
5. [Pengujian Otomatis (Automated Testing)](#5-pengujian-otomatis-automated-testing)
6. [Batasan Sistem & Catatan Deployment](#6-batasan-sistem--catatan-deployment)

---

## 1. INFORMASI UMUM & ARSITEKTUR

### Identitas Branding Resmi
- **Nama Aplikasi**: `Hsse Manajemen`
- **Sub-Brand / Unit Organisasi**: `Project Manajemen Geodipa Unit Dieng 2`
- **Tujuan Sistem**: Menyediakan platform terpadu pencatatan, pemantauan real-time, evaluasi indikator keselamatan kerja, pengelolaan logistik alat keselamatan kerja, serta pelaporan audit operasional panas bumi yang patuh terhadap regulasi Ditjen EBTKE Kementerian ESDM dan regulasi K3 Nasional.

### Stack Teknologi
- **Backend Framework**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL 8.0+ / MariaDB 10.4+
- **Frontend / UI**: Laravel Blade Templates, Bootstrap 5.3, Bootstrap Icons
- **Interaktivitas & Visualisasi**: Alpine.js, Chart.js 4.x
- **Pemrosesan Gambar**: PHP GD / Intervention Image compatible (`ImageUploadService`)
- **Autentikasi & Otorisasi**: Laravel Session & Middleware (`auth`, `role:Admin`)

---

## 2. PANDUAN INSTALASI & KONFIGURASI

### Kebutuhan Sistem
- PHP >= 8.2 dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`, `xml`
- Composer >= 2.x
- Web Server: Apache (Laragon / XAMPP) atau Nginx
- MySQL >= 8.0 atau MariaDB >= 10.4

### Langkah Instalasi

1. **Clone atau Letakkan Repositori**:
   Letakkan repositori di direktori web server (misalnya `c:\laragon\www\HSSE PROJECT`).

2. **Salin Environment**:
   ```bash
   cp .env.example .env
   ```

3. **Konfigurasi Database pada `.env`**:
   ```env
   APP_NAME="Hsse Manajemen"
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_TIMEZONE="Asia/Jakarta"
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=db_hsse
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Instal Dependensi & Generate Key**:
   ```bash
   composer install
   php artisan key:generate
   ```

5. **Jalankan Migrasi Database**:
   > **PENTING**: Jangan pernah menggunakan `php artisan migrate:fresh` atau `db:wipe` pada database produksi karena akan menghapus data historis operasional. Gunakan migrasi inkremental:
   ```bash
   php artisan migrate
   ```

6. **Buat Tautan Simbolik Storage**:
   ```bash
   php artisan storage:link
   ```

7. **Jalankan Development Server**:
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui peramban di `http://127.0.0.1:8000`.

### Kredensial Akun Pengujian (Default)
| Role | Email | Password | Hak Akses |
|---|---|---|---|
| **Admin** | `admin@geodipa.co.id` | `password` | Akses penuh seluruh modul, edit data master, koreksi Man Hours, manajemen user |
| **User/Operator** | `user@geodipa.co.id` | `password` | Input transaksi, peminjaman barang, lapor insiden, monitoring data |

---

## 3. DOKUMENTASI MODUL & FITUR

### 3.1 Dashboard Utama
- **Fungsi**: Menampilkan ringkasan eksekutif seluruh indikator keselamatan kerja PT Geo Dipa Energi Unit Dieng 2.
- **Karakteristik & Formula**:
  - **LTIFR (Lost Time Injury Frequency Rate)**:
    $$\text{LTIFR} = \frac{\text{Jumlah Kasus LTI} \times 1.000.000}{\text{Total Akumulasi Man Hours}}$$
  - **LTISR (Lost Time Injury Severity Rate)**:
    $$\text{LTISR} = \frac{\text{Total Hari Kerja Hilang} \times 1.000.000}{\text{Total Akumulasi Man Hours}}$$
  - Menangani proteksi pembagian nol (*division by zero safe*) saat total jam kerja masih 0.
  - Seluruh widget kartu, donut chart kategori, dan grafik tren dihitung secara real-time dari basis data tanpa angka tiruan (*zero dummy data*).

---

### 3.2 Modul Inventory
Modul pengelolaan alat pelindung diri (APD), instrumen lapangan, dan logistik keselamatan kerja dengan tata letak responsif 3 Tab Navigasi.

#### Konsep Stok Fisik vs Stok Bersih Siap Pakai:
1. **Stok Fisik Gudang (`stock`)**: Total kuantitas aset yang dimiliki PT Geo Dipa Energi Unit Dieng 2 (berasal dari mutasi masuk - mutasi keluar).
2. **Kuantitas Terpinjam (`borrowed_stock`)**: Total alat yang sedang dibawa oleh pekerja/tim di lapangan (status aktif: `Dipinjam` atau `Sebagian Dikembalikan`).
3. **Stok Bersih Siap Pakai (`available_stock`)**:
   $$\text{Stok Tersedia} = \max(0, \text{Stok Fisik} - \text{Kuantitas Terpinjam})$$
   *Peminjaman barang tidak mengurangi kolom stok fisik aset gudang, sehingga mencegah double-deduction (pengurangan ganda).*

#### Tab Navigasi Inventory:
1. **Master Barang & Stok Real-Time**:
   - Menampilkan katalog barang, kode aset, kategori dari 7 kelompok standar HSSE, batas minimum stok, dan lokasi penyimpanan.
   - Dilengkapi dialog modal rincian Alpine.js untuk audit cepat (*Barang Masuk, Barang Keluar, Stok Aman, Reorder Alert*).
2. **Riwayat Transaksi (History)**:
   - Pencatatan mutasi fisik Masuk (*In*), Keluar (*Out*), dan Penyesuaian (*Adjustment*).
   - Filter parameter: pencarian teks, jenis transaksi, kategori master, dan rentang tanggal.
3. **Barang Terpinjam (Active & History)**:
   - Menampilkan 4 kartu ringkasan: Peminjaman Aktif, Total Unit Terpinjam, Jenis Alat Terpinjam, dan Peringatan Terlambat (*Overdue*).
   - **Form Peminjaman**: Memvalidasi bahwa kuantitas pinjam $\le$ Stok Bersih Tersedia.
   - **Proteksi Barang Habis Pakai**: Menolak peminjaman untuk barang kategori *Habis Pakai, Consumable, atau P3K/Medis* (diarahkan ke menu Mutasi Keluar).
   - **Pengembalian Parsial & Penuh**:
     - *Pengembalian Sebagian*: Mengurangi sisa kewajiban pinjam, mengubah status menjadi `Sebagian Dikembalikan`.
     - *Pengembalian Penuh*: Saat sisa mencapai 0, status berubah menjadi `Dikembalikan` dan otomatis keluar dari daftar peminjaman aktif.

#### Export Excel / CSV:
- Tombol **Export Excel** pada header Inventory secara default **mengekspor Riwayat Transaksi (History)** mutasi barang lengkap dengan UTF-8 BOM agar rapi saat dibuka di Microsoft Excel.

---

### 3.3 Modul Incident (Kecelakaan Kerja)
Modul investigasi, pencatatan, dan pelaporan kecelakaan kerja operasional panas bumi.

- **Identitas Visual**: Mempertahankan identitas tema warna **Merah Pekat (`#dc2626`)** pada header, ikon, badge, aksen visual, dan grafik.
- **Klasifikasi Area Biner (ESDM)**:
  - Kecelakaan Panas Bumi (Wilayah Kerja Kuasa Pertambangan Panas Bumi).
  - Kecelakaan Non-Panas Bumi (Fasilitas Penunjang, Perkantoran, dan Logistik).
- **Multi-Tags Kategori**:
  - Kategori Umum: *Nearmiss, First Aid, Medical Treatment, Restricted Work, Lost Time Injury, Fatality, Property Damage, Environmental*.
  - Kategori EBTKE (ESDM): *Ringan, Berat, Mati*.
- **Metrik & Visualisasi Total Hari Hilang (Lost Workdays - LWD)**:
  - Kartu statistik khusus memvisualisasikan total hari kerja yang hilang.
  - Jika 0 hari hilang: Menampilkan status informatif *Nihil Hari Hilang (Zero Lost Workdays)* berwarna hijau/ramah.
  - Jika > 0 hari hilang: Menampilkan grafik batang merah kombinasi garis kasus per bulan dan ringkasan dampak operasional.
  - Seluruh donat chart interaktif dapat diklik untuk membuka modal inspeksi instan.

---

### 3.4 Modul Daily Work
- Pencatatan agenda pekerjaan harian tim HSSE lapangan.
- Meliputi pencatatan lokasi, shift kerja, uraian aktivitas, potensi bahaya, tindakan mitigasi, penanggung jawab, serta upload foto dokumentasi sebelum dan sesudah pekerjaan (*Before-After*).

---

### 3.5 Modul HSSE Plan
- Perencanaan dan evaluasi program kerja keselamatan kerja tahunan/bulanan.
- Mencakup program kerja, PIC, target implementasi, realisasi fisik lapangan, persentase ketercapaian program, serta status (*Belum Berjalan, On Progress, Terealisasi*).

---

### 3.6 Modul Events
- Pengelolaan kegiatan keselamatan kerja seperti *Toolbox Meeting, HSSE Induction, Safety Drill/Emergency Simulation, Training Sertifikasi, Safety Walkthrough*, dan *Audit Internal/Eksternal*.
- Menampilkan jadwal, daftar hadir, serta dokumentasi kegiatan.

---

### 3.7 Modul Workforce & Man Hours
- **Workforce (Tenaga Kerja)**:
  - Pencatatan jumlah pekerja harian per unit operasi (Power Plant Dieng, Wellpad/Drilling, Proyek Dieng 2, Kantor Administrasi).
  - Komposisi tenaga kerja: Karyawan Organik, Kontraktor/Mitra Kerja, dan Subkontraktor.
- **Man Hours (Jam Kerja Selamat)**:
  - **Man Hours Harian**:
    $$\text{Man Hours Harian} = \text{Jumlah Pekerja} \times \text{Jam Kerja Standar per Orang (default 8 jam)}$$
  - **Total Akumulasi Man Hours Unit (Bisa Diedit Manual)**:
    - Pengguna berwenang (Admin) dapat mengklik ikon pensil pada kolom *Total Akumulasi Man Hours Unit* untuk melakukan penyesuaian/koreksi akumulasi historis.
    - Nilai tersimpan langsung ke database tanpa perlu menginput ulang data harian.
  - **Indikator Keselamatan**: Otomatis memperbarui perhitungan jam kerja tanpa kecelakaan (*Safe Man Hours*) untuk unit Dieng 2.

---

### 3.8 Modul Issue & Follow Up
Modul tindak lanjut temuan bahaya dan ketidaksesuaian (*Hazard Identification & Corrective Action*).

- **4 Divisi Resmi**:
  1. `H — Health & Savety` (Kode Database: `H`)
  2. `S — Sosial` (Kode Database: `S_SOCIAL`)
  3. `S — Scurity` (Kode Database: `S_SECURITY`)
  4. `E — Emvironment` (Kode Database: `E`)
- **Penanganan Kode Database Unik**:
  - Karena terdapat dua divisi berawalan huruf 'S', sistem menggunakan kode unik internal (`S_SOCIAL` dan `S_SECURITY`) untuk menjamin integritas relasi dan query data, dengan tetap menampilkan label ejaan resmi sesuai kebutuhan unit.
- **Kartu Statistik**: 4 kartu statistik divisi berdampingan ditambah 1 kartu total keseluruhan issue.
- **Siklus Hidup Status**: `Open` $\rightarrow$ `In Progress` (otomatis saat ada follow up baru) $\rightarrow$ `Closed` (bisa di-reopen jika temuan berulang).

---

### 3.9 Modul Gasdetektor
Modul pemantauan pengujian dan kalibrasi detektor gas multi-gas di area sumur dan pembangkit listrik panas bumi.

- **Parameter Gas yang Dipantau**:
  - $H_2S$ (Hidrogen Sulfida - ppm)
  - $CH_4$ / LEL (Metana / Gas Mudah Terbakar - %)
  - $CO$ (Karbon Monoksida - ppm)
  - $O_2$ (Oksigen - %)
- **Penyederhanaan UI/UX**:
  - Tab "Inventaris Perangkat" telah dilepas dari tampilan untuk menyederhanakan alur kerja pengguna.
  - Filter periode Bulan dan Tahun yang presisi.
  - Tombol ekspor data Excel tunggal yang bersih (*single export button*).

---

### 3.10 Modul Manajemen Pengguna & Audit Log
- **Role-Based Access Control**:
  - `Admin`: Akses penuh konfigurasi master, koreksi Man Hours, hapus data, kelola user.
  - `User`: Akses operasional pelaporan dan peminjaman logistik.
- **Proteksi Admin Terakhir**: Mencegah deaktifasi atau penghapusan satu-satunya akun Admin aktif.
- **Activity Log**: Mencatat setiap aktivitas penting (*Tambah, Ubah, Hapus, Peminjaman, Pengembalian*) lengkap dengan waktu, IP address, user agent, dan metadata perubahan record.

---

## 4. STANDARDISASI MEDIA & UPLOAD GAMBAR

Aplikasi dilengkapi dengan layanan mandiri **`App\Services\ImageUploadService`** untuk menjamin konsistensi file upload:

1. **Format File yang Didukung**:
   - Gambar: `.jpg`, `.jpeg`, `.png`, `.webp`, `.heic`, `.heif`
   - Dokumen: `.pdf` (pada lampiran laporan)
2. **Validasi & Keamanan**:
   - Pengecekan MIME type asli dan proteksi file executable yang disamarkan.
3. **Kompresi & Optimalisasi Ukuran**:
   - Gambar otomatis di-*resize* ke batas maksimal 1920px untuk gambar utama dan 300px untuk *thumbnail*.
   - Tingkat kompresi otomatis disesuaikan agar ukuran file efisien (< 500 KB) tanpa mengurangi keterbacaan bukti investigasi lapangan.
4. **Struktur Direktori Storage**:
   - Disimpan di `storage/app/public/` per modul:
     - `public/incidents/`
     - `public/inventory/`
     - `public/daily-work/`
     - `public/issues/`
     - `public/events/`
5. **Metadata di Database**: Menyimpan nama file, path relatif, ukuran asli, dan MIME type.

---

## 5. PENGUJIAN OTOMATIS (AUTOMATED TESTING)

Aplikasi memiliki rangkaian **Automated Tests** yang mencakup pengujian Unit dan Feature:

```bash
# Menjalankan seluruh test suite
php artisan test

# Menjalankan pengujian modul spesifik
php artisan test --filter=InventoryBorrowingTest
php artisan test --filter=IncidentManagementTest
php artisan test --filter=DashboardComprehensiveTest
php artisan test --filter=IssueFourDivisionsTest
php artisan test --filter=ManHoursAccumulationTest
php artisan test --filter=GasdetektorIntegrationTest
```

**Status Pengujian Terkini**:
- **Total Test**: 157 Passed
- **Total Assertions**: 1.057 Assertions
- **Tingkat Kelulusan**: **100% PASS**

---

## 6. BATASAN SISTEM & CATATAN DEPLOYMENT

1. **Integritas Data Historis**:
   - Jangan pernah menjalankan `php artisan migrate:fresh` pada environment *production* atau *staging*. Seluruh skema database dirancang aditif dan mempertahankan data historis operasional.
2. **Koneksi MySQL Local (Laragon)**:
   - Database default adalah `db_hsse`. Pastikan MySQL service di Laragon aktif sebelum menjalankan dev server.
3. **Penyimpanan Simbolik (`storage:link`)**:
   - Jika foto dokumentasi tidak muncul setelah deployment server baru, jalankan `php artisan storage:link`.
4. **Ikon Notifikasi Global**:
   - Sesuai arahan UI/UX, ikon lonceng notifikasi pada top navigation bar telah dinonaktifkan demi kebersihan tampilan header.

---
**PT Geo Dipa Energi (Persero) Unit Dieng 2**  
*Health, Safety, Security, and Environment Management System*
