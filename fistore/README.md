# FISTORE - Website Toko Buku (HTML, CSS, PHP + MySQL)

## Cara Menjalankan di XAMPP

1. **Copy folder**
   Salin seluruh folder `fistore` ke dalam `C:\xampp\htdocs\` (Windows) atau `/Applications/XAMPP/htdocs/` (Mac).
   Hasil akhirnya: `htdocs/fistore/index.php`, dst.

2. **Nyalakan XAMPP**
   Buka XAMPP Control Panel, start **Apache** dan **MySQL**.

3. **Import database**
   - Buka `http://localhost/phpmyadmin`
   - Klik tab **Import** → pilih file `database.sql` dari folder `fistore`
   - Klik **Go**. Database `fistore_db` beserta seluruh tabel & data contoh buku akan otomatis dibuat.

4. **Buat akun demo**
   Buka `http://localhost/fistore/setup.php` di browser (jalankan sekali saja).
   Ini akan membuat 2 akun otomatis:
   - **Admin** → email: `admin@fistore.com` / password: `admin123`
   - **User** → email: `user@fistore.com` / password: `user123`

   Kamu juga bisa daftar akun user baru sendiri lewat halaman **register.php**.

5. **Buka website**
   Akses `http://localhost/fistore/` di browser.

## Struktur Halaman

**Untuk pengunjung / user:**
- `index.php` — Home (banner diskon, What's New, Best Seller, Trending Now)
- `books.php` — Semua buku + filter kategori + pencarian
- `book_detail.php` — Detail buku, tambah ke keranjang & wishlist
- `wishlist.php` — Daftar buku favorit (khusus user login)
- `cart.php` — Keranjang belanja
- `checkout.php` → `process_checkout.php` — Proses transaksi
- `receipt.php` — Bukti pembayaran / struk (bisa dicetak)
- `orders.php` — Riwayat pesanan user
- `about.php` — Halaman statis di footer

**Untuk admin** (folder `/admin`):
- `dashboard.php` — Statistik toko (total buku, user, transaksi, pendapatan, stok menipis)
- `books.php` — Kelola buku (lihat, cari, hapus)
- `add_book.php` / `edit_book.php` — Tambah / edit buku, atur kategori tampilan (New/Best Seller/Trending)
- `orders.php` / `order_detail.php` — Lihat semua transaksi pembeli

## Cara Jadi Admin
Admin dan customer sekarang punya **halaman login terpisah dan session terpisah**, jadi kamu bisa login sebagai admin di satu tab dan login sebagai user biasa di tab lain (browser sama) tanpa saling menimpa.

- Login **admin**: `http://localhost/fistore/admin/login.php` (akun dengan role admin hanya bisa login di sini)
- Login **customer**: `http://localhost/fistore/login.php` (akun admin tidak bisa login lewat halaman ini — akan diarahkan ke halaman admin)

Kalau kamu login admin lalu refresh halaman admin sambil tetap browsing/daftar akun user di tab lain, session admin tidak akan berubah lagi — keduanya independen.

Login admin: `admin@fistore.com` / `admin123` (dibuat lewat `setup.php`). Akun baru yang mendaftar lewat `register.php` selalu berrole `user` demi keamanan.

## Alur Transaksi
1. User login → pilih buku → **Tambah ke Keranjang**
2. Buka **Keranjang** → centang buku yang mau di-checkout (bisa pilih sebagian, sisanya tetap tersimpan di keranjang) → **Checkout Buku Terpilih**
3. Isi data penerima & metode pembayaran → **Buat Pesanan**
4. Sistem membuat nomor invoice, mengurangi stok buku, dan menghapus hanya buku yang dipilih dari keranjang (status awal pesanan: **Menunggu Konfirmasi**)
5. User diarahkan ke halaman **Struk/Bukti Pembayaran** (bisa dicetak / disimpan)
6. Riwayat transaksi & status terkini bisa dilihat lagi lewat **Pesanan Saya**

## Metode Pembayaran
Di halaman checkout tersedia:
- **Transfer Bank** — pilih bank (BCA / BNI / BRI / Mandiri); nomor virtual account contoh tampil di struk
- **Kartu Kredit** — isi nomor kartu, nama, masa berlaku, dan CVV (divalidasi format, Luhn, dan tanggal kedaluwarsa). Hanya 4 digit terakhir kartu yang disimpan di struk; nomor lengkap dan CVV tidak pernah disimpan.
- **E-Wallet** — pilih OVO / GoPay / Dana; tampil frame QR code (sementara kosong, taruh gambar QR di `assets/img/qr-ewallet.png` dan otomatis muncul)
- **COD**

### Bukti Pembayaran (Transfer Bank & E-Wallet)
1. Saat memilih **Transfer Bank** atau **E-Wallet** di checkout, muncul form **Bukti Pembayaran** (nama pengirim + foto bukti JPG/PNG/WEBP, maks 3MB) yang wajib diisi sebelum **Buat Pesanan**. Untuk transfer bank, rekening tujuan tampil di checkout (ganti nomornya di `storeBankAccounts()` pada `includes/functions.php`).
   - Gambar (JPG/PNG/WEBP) otomatis **dikompres** (sisi terpanjang maks 1600px) dan **dikonversi ke PDF** A4 berisi info invoice di atasnya. File PDF dari user disimpan apa adanya. Tidak ada batas ukuran dari aplikasi; batas dari server diatur di `.htaccess` (64MB) atau `php.ini`. Butuh extension PHP **GD** (aktif default di XAMPP).
2. Status pembayaran: **Menunggu Verifikasi → Terverifikasi** (atau **Ditolak**, lalu user upload ulang dari halaman Struk / Pesanan Saya).
3. Admin membuka **Pesanan → Detail**, melihat bukti (bisa langsung **Cetak Bukti Pembayaran**), lalu **Konfirmasi Pembayaran** (pesanan otomatis jadi *Diproses*) atau **Tolak Bukti** dengan alasan.
4. Pesanan Transfer/E-Wallet tidak bisa diproses sebelum pembayarannya dikonfirmasi.
5. Database lama: jalankan `migration_add_payment_proof.sql` sekali di phpMyAdmin. Database baru: import `database.sql` seperti biasa.

Catatan: ini simulasi untuk proyek lokal, belum terhubung ke payment gateway sungguhan. Untuk transaksi nyata, gunakan layanan seperti Midtrans/Xendit agar data kartu tidak diproses langsung oleh server toko.

## Status Pesanan
Setiap transaksi punya status yang mengikuti alur toko online pada umumnya:
- **Menunggu Konfirmasi** — status awal begitu user checkout
- **Diproses** — admin sedang menyiapkan pesanan
- **Selesai** — pesanan sudah sampai / transaksi tuntas
- **Dibatalkan** — pesanan dibatalkan

Admin bisa mengubah status ini dari **Pesanan → Detail** di panel admin (dropdown + tombol "Simpan Status"). Status yang tampil ke user (di halaman "Pesanan Saya" & struk) otomatis mengikuti perubahan yang dibuat admin. Daftar pesanan admin juga bisa difilter per status.

**Kalau kamu sudah pernah import `database.sql` versi lama**, kolom `status` di tabel `orders` sudah ada dari awal jadi tidak perlu migrasi tambahan — cukup pastikan file PHP-nya sudah versi terbaru.

## Foto Sampul Buku (Admin)
Admin sekarang bisa unggah foto sampul asli lewat **Tambah Buku** / **Edit Buku**:
- Format yang didukung: JPG, PNG, WEBP, GIF — maksimal 2MB.
- Foto disimpan di folder `uploads/covers/`.
- Kalau tidak upload foto, card buku otomatis memakai kartu warna + judul seperti sebelumnya (warna bisa dipilih di field "Warna Cover").
- Saat edit, admin bisa mengganti foto (otomatis menghapus foto lama) atau mencentang "Hapus foto ini" untuk kembali ke tampilan warna.
- Saat buku dihapus, foto sampulnya juga otomatis dihapus dari server.

**Penting untuk instalasi baru:** pastikan folder `uploads/covers/` bisa ditulis oleh web server (biasanya sudah otomatis bisa di XAMPP lokal). Kalau upload gagal dengan pesan "Gagal menyimpan foto ke server", cek permission folder tersebut.

**Kalau kamu sudah pernah import `database.sql` versi lama** (sebelum fitur ini ada), jalankan `migration_add_cover_image.sql` lewat phpMyAdmin (tab SQL) agar tabel `books` punya kolom `cover_image`. Instalasi baru cukup import `database.sql` terbaru, tidak perlu file migrasi ini.

## Catatan
- Tema warna: light pink / peach, konsisten di semua halaman (lihat `assets/css/style.css`).
- Setelah selesai testing, disarankan menghapus atau memberi proteksi pada `setup.php` agar tidak disalahgunakan.
- Semua query menggunakan PDO prepared statements untuk mencegah SQL Injection, dan password di-hash dengan `password_hash()`.
