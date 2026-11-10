<?php
// =========================================================
// FISTORE - Fungsi bantu (auth, format, dsb)
// =========================================================

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        $prefix = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '' : 'admin/';
        header('Location: ' . $prefix . 'login.php');
        exit;
    }
}

// Mengembalikan path relatif yang benar baik dipanggil dari root maupun /admin
function base_url($path = '') {
    $prefix = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' : '';
    return $prefix . $path;
}

function formatRupiah($num) {
    return 'Rp' . number_format((float)$num, 0, ',', '.');
}

function cartCount($pdo) {
    if (!isLoggedIn()) return 0;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $r = $stmt->fetch();
    return (int)$r['total'];
}

function wishlistCount($pdo) {
    if (!isLoggedIn()) return 0;
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $r = $stmt->fetch();
    return (int)$r['total'];
}

function isInWishlist($pdo, $bookId) {
    if (!isLoggedIn()) return false;
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$_SESSION['user_id'], $bookId]);
    return (bool)$stmt->fetch();
}

function generateInvoiceCode() {
    return 'FST-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Daftar status pesanan yang valid, dari awal transaksi sampai selesai.
function orderStatusList() {
    return ['Menunggu Konfirmasi', 'Diproses', 'Selesai', 'Dibatalkan'];
}

// Kelas CSS badge untuk tiap status pesanan.
function orderStatusClass($status) {
    $map = [
        'Menunggu Konfirmasi' => 'status-pending',
        'Diproses' => 'status-process',
        'Selesai' => 'status-done',
        'Dibatalkan' => 'status-cancelled',
    ];
    return $map[$status] ?? 'status-pending';
}

// Status berikutnya dalam alur pesanan (dipakai tombol "Perbarui Status" satu klik).
// Mengembalikan null kalau status sudah final (Selesai / Dibatalkan).
function orderNextStatus($current) {
    $flow = ['Menunggu Konfirmasi', 'Diproses', 'Selesai'];
    $idx = array_search($current, $flow, true);
    if ($idx === false || $idx === count($flow) - 1) {
        return null;
    }
    return $flow[$idx + 1];
}

// Validasi nomor kartu dengan algoritma Luhn.
function luhnValid($number) {
    $sum = 0;
    $alt = false;
    for ($i = strlen($number) - 1; $i >= 0; $i--) {
        $n = (int)$number[$i];
        if ($alt) {
            $n *= 2;
            if ($n > 9) $n -= 9;
        }
        $sum += $n;
        $alt = !$alt;
    }
    return $sum % 10 === 0;
}

// Menangani upload foto sampul buku. $uploadDir harus path absolut ke folder uploads/covers.
// Mengembalikan ['ok' => bool, 'filename' => string|null, 'error' => string|null]
function handleCoverUpload($file, $uploadDir) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'filename' => null, 'error' => null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'filename' => null, 'error' => 'Gagal mengunggah foto sampul.'];
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : $file['type'];

    if (!isset($allowed[$mime])) {
        return ['ok' => false, 'filename' => null, 'error' => 'Format foto harus JPG, PNG, WEBP, atau GIF.'];
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        return ['ok' => false, 'filename' => null, 'error' => 'Ukuran foto maksimal 2MB.'];
    }
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'cover_' . date('YmdHis') . '_' . substr(uniqid(), -6) . '.' . $allowed[$mime];
    $target = rtrim($uploadDir, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['ok' => false, 'filename' => null, 'error' => 'Gagal menyimpan foto ke server. Pastikan folder uploads/covers bisa ditulis.'];
    }

    return ['ok' => true, 'filename' => $filename, 'error' => null];
}

function deleteCoverImage($filename, $uploadDir) {
    if ($filename) {
        $path = rtrim($uploadDir, '/') . '/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

// ---------------- Bukti pembayaran (Transfer Bank & E-Wallet) ----------------

// Transfer Bank & E-Wallet wajib kirim bukti bayar; Kartu Kredit & COD tidak.
function isPrepaidMethod($method) {
    return strpos($method, 'Transfer Bank') === 0 || strpos($method, 'E-Wallet') === 0;
}

function isEwalletMethod($method) {
    return strpos($method, 'E-Wallet') === 0;
}

// Nama e-wallet dari "E-Wallet - GoPay" (kosong kalau tidak disebut).
function ewalletName($method) {
    return preg_match('/^E-Wallet - (.+)$/', $method, $m) ? $m[1] : '';
}

function paymentStatusClass($status) {
    $map = [
        'Belum Dibayar' => 'status-pending',
        'Menunggu Verifikasi' => 'status-process',
        'Terverifikasi' => 'status-done',
        'Ditolak' => 'status-cancelled',
    ];
    return $map[$status] ?? 'status-pending';
}

// User boleh (meng-upload ulang) bukti bayar selama belum diverifikasi.
function orderCanUploadProof($order) {
    return isPrepaidMethod($order['payment_method'])
        && $order['status'] === 'Menunggu Konfirmasi'
        && in_array($order['payment_status'], ['Belum Dibayar', 'Ditolak'], true);
}

// Pesanan Transfer/E-Wallet baru boleh diproses setelah pembayarannya diverifikasi admin.
function orderCanAdvance($order) {
    if (orderNextStatus($order['status']) === null) return false;
    if (isPrepaidMethod($order['payment_method']) && $order['status'] === 'Menunggu Konfirmasi') {
        return $order['payment_status'] === 'Terverifikasi';
    }
    return true;
}

// Menangani upload bukti pembayaran: gambar otomatis dikompres & dikonversi ke PDF, tanpa batas ukuran file
// (selain batas php.ini). $uploadDir = path absolut uploads/proofs. $meta = invoice/sender/method/amount untuk header PDF.
// Mengembalikan ['ok' => bool, 'filename' => string|null, 'error' => string|null]
function handleProofUpload($file, $uploadDir, $meta = []) {
    $fail = function ($msg) { return ['ok' => false, 'filename' => null, 'error' => $msg]; };

    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $fail('Pilih file bukti pembayaran dulu.');
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return $fail('File melebihi batas server (upload_max_filesize / post_max_size di php.ini). Naikkan batasnya atau kompres file dulu.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $fail('Gagal mengunggah bukti pembayaran.');
    }
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : $file['type'];
    $base = 'proof_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.pdf';
    $target = rtrim($uploadDir, '/') . '/' . $base;

    // PDF dari user (mis. e-statement m-banking) disimpan apa adanya.
    if ($mime === 'application/pdf') {
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return $fail('Gagal menyimpan file ke server. Pastikan folder uploads/proofs bisa ditulis.');
        }
        return ['ok' => true, 'filename' => $base, 'error' => null];
    }

    // Gambar -> dikompres lalu dikonversi otomatis ke PDF.
    $loaders = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'];
    if (!isset($loaders[$mime])) {
        return $fail('Format bukti harus JPG, PNG, WEBP, atau PDF.');
    }
    if (!extension_loaded('gd') || !function_exists($loaders[$mime])) {
        return $fail('Ekstensi PHP GD belum aktif di server, jadi gambar tidak bisa dikompres ke PDF. Aktifkan extension=gd di php.ini.');
    }
    $info = @getimagesize($file['tmp_name']);
    if (!$info || $info[0] < 1 || $info[1] < 1) {
        return $fail('File gambar tidak bisa dibaca.');
    }
    if ($info[0] * $info[1] > 80000000) {
        return $fail('Resolusi gambar terlalu besar untuk diproses.');
    }

    @ini_set('memory_limit', '768M');
    @set_time_limit(60);

    $src = @$loaders[$mime]($file['tmp_name']);
    if (!$src) {
        return $fail('File gambar rusak atau tidak bisa diproses.');
    }

    // Putar sesuai orientasi EXIF (foto dari HP)
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($file['tmp_name']);
        $angle = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
        if ($angle) {
            $rot = imagerotate($src, $angle, 0);
            if ($rot) { imagedestroy($src); $src = $rot; }
        }
    }

    // Perkecil (sisi terpanjang maks 1600px) di atas latar putih, lalu simpan sebagai JPEG kualitas 72.
    $w = imagesx($src); $h = imagesy($src);
    $scale = min(1, 1600 / max($w, $h));
    $nw = max(1, (int)round($w * $scale)); $nh = max(1, (int)round($h * $scale));
    $dst = imagecreatetruecolor($nw, $nh);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($src);
    ob_start();
    imagejpeg($dst, null, 72);
    $jpeg = ob_get_clean();
    imagedestroy($dst);

    $pdf = buildProofPdf($jpeg, $nw, $nh, $meta);
    if (file_put_contents($target, $pdf) === false) {
        return $fail('Gagal menyimpan file ke server. Pastikan folder uploads/proofs bisa ditulis.');
    }
    return ['ok' => true, 'filename' => $base, 'error' => null];
}

// Teks untuk PDF (Helvetica / WinAnsi): ubah encoding dan escape karakter khusus.
function pdfEsc($s) {
    $s = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', (string)$s);
    if ($s === false) $s = '';
    return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $s);
}

// Membuat PDF A4 satu halaman: judul + info pesanan + gambar bukti (JPEG) di bawahnya. Tanpa library tambahan.
function buildProofPdf($jpeg, $w, $h, $meta = []) {
    $lines = [
        'Invoice' => $meta['invoice'] ?? '-',
        'Pengirim' => $meta['sender'] ?? '-',
        'Metode' => $meta['method'] ?? '-',
        'Nominal' => isset($meta['amount']) ? formatRupiah($meta['amount']) : '-',
        'Diunggah' => date('d M Y, H:i'),
    ];
    $c = "BT /F2 16 Tf 40 795 Td (" . pdfEsc('BUKTI PEMBAYARAN - FISTORE') . ") Tj ET\n";
    $y = 772;
    foreach ($lines as $k => $v) {
        $c .= sprintf("BT /F2 10 Tf 40 %d Td (%s) Tj ET\nBT /F1 10 Tf 110 %d Td (: %s) Tj ET\n", $y, pdfEsc($k), $y, pdfEsc($v));
        $y -= 16;
    }
    $boxW = 515; $boxH = $y - 40; $top = $y;
    $s = min($boxW / $w, $boxH / $h);
    $dw = $w * $s; $dh = $h * $s;
    $c .= sprintf("q %.2F 0 0 %.2F %.2F %.2F cm /Im1 Do Q\n", $dw, $dh, 40 + ($boxW - $dw) / 2, $top - $dh);

    $o = [];
    $o[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $o[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $o[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R /F2 7 0 R >> /XObject << /Im1 6 0 R >> >> /Contents 4 0 R >>";
    $o[4] = "<< /Length " . strlen($c) . " >>\nstream\n" . $c . "endstream";
    $o[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
    $o[6] = "<< /Type /XObject /Subtype /Image /Width $w /Height $h /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($jpeg) . " >>\nstream\n" . $jpeg . "\nendstream";
    $o[7] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

    $pdf = "%PDF-1.4\n";
    $off = [];
    foreach ($o as $n => $body) {
        $off[$n] = strlen($pdf);
        $pdf .= "$n 0 obj\n$body\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($o) + 1) . "\n0000000000 65535 f \n";
    foreach ($off as $n => $pos) {
        $pdf .= sprintf("%010d 00000 n \n", $pos);
    }
    $pdf .= "trailer\n<< /Size " . (count($o) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    return $pdf;
}

function proofIsPdf($filename) {
    return strtolower(pathinfo((string)$filename, PATHINFO_EXTENSION)) === 'pdf';
}

// Tampilan bukti: PDF -> kartu link (atau pratinjau iframe), gambar lama -> thumbnail.
function proofViewHtml($filename, $baseUrl, $preview = false) {
    $url = e($baseUrl . $filename);
    if (proofIsPdf($filename)) {
        $card = '<a href="' . $url . '" target="_blank" class="proof-file-card"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg><span><b>Bukti pembayaran (PDF)</b><small>Klik untuk membuka</small></span></a>';
        return $preview ? $card . '<iframe class="proof-frame" src="' . $url . '" title="Bukti pembayaran"></iframe>' : $card;
    }
    return '<a href="' . $url . '" target="_blank" class="proof-thumb"><img src="' . $url . '" alt="Bukti pembayaran"></a>';
}

function deleteProofImage($filename, $uploadDir) {
    if ($filename) {
        $path = rtrim($uploadDir, '/') . '/' . basename($filename);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

// Rekening tujuan transfer toko. GANTI dengan nomor rekening asli toko.
function storeBankAccounts() {
    return [
        'BCA'     => '738 204 5916',
        'BNI'     => '052 917 3846',
        'BRI'     => '3021 01 482915 53 7',
        'Mandiri' => '137 00 29584613 8',
    ];
}

function storeBankHolder() {
    return 'FISTORE';
}
