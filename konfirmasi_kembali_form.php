<?php
session_start();
include 'config.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'petugas') {
    header("Location: index.php");
    exit();
}

// --- BAGIAN PERBAIKAN OTOMATIS (VERSI AMAN) ---
$result = mysqli_query($conn, "SHOW COLUMNS FROM peminjaman WHERE Field = 'status_transaksi'");
$row = mysqli_fetch_assoc($result);
$type = $row['Type']; 

if (strpos($type, "'selesai'") === false) {
    mysqli_query($conn, "ALTER TABLE peminjaman MODIFY COLUMN status_transaksi ENUM('proses', 'disetujui', 'menunggu_kembali', 'ditolak', 'selesai') DEFAULT 'proses'");
}

$check_cols = mysqli_query($conn, "SHOW COLUMNS FROM peminjaman LIKE 'tgl_kembali_asli'");
if (mysqli_num_rows($check_cols) == 0) {
    mysqli_query($conn, "ALTER TABLE peminjaman ADD COLUMN laporan_kerusakan TEXT DEFAULT NULL");
    mysqli_query($conn, "ALTER TABLE peminjaman ADD COLUMN denda_kerusakan INT(11) DEFAULT 0");
    mysqli_query($conn, "ALTER TABLE peminjaman ADD COLUMN denda INT(11) DEFAULT 0"); 
    mysqli_query($conn, "ALTER TABLE peminjaman ADD COLUMN tgl_kembali_asli DATETIME DEFAULT NULL");
}

$check_pay = mysqli_query($conn, "SHOW COLUMNS FROM peminjaman LIKE 'status_pembayaran'");
if (mysqli_num_rows($check_pay) == 0) {
    mysqli_query($conn, "ALTER TABLE peminjaman ADD COLUMN status_pembayaran ENUM('belum_bayar', 'lunas', 'proses_verifikasi') DEFAULT 'lunas'");
}
// ----------------------------------------------

// Ambil ID Peminjaman dari URL
if (!isset($_GET['id_p'])) {
    header("Location: dashboard_admin.php");
    exit();
}

$id_p = $_GET['id_p'];

// Ambil data lengkap peminjaman
$query = mysqli_query($conn, "SELECT p.*, u.username, u.no_induk, a.nama_alat, a.id AS id_alat 
                              FROM peminjaman p 
                              JOIN users u ON p.user_id = u.id 
                              JOIN alat a ON p.alat_id = a.id 
                              WHERE p.id = '$id_p'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data peminjaman tidak ditemukan.");
}

// Jika form disubmit
if (isset($_POST['simpan_konfirmasi'])) {
    $denda_kerusakan = $_POST['denda_kerusakan'];
    $denda_telat = $_POST['denda_telat']; // Input manual denda telat
    $id_alat = $data['id_alat'];

    // Status bayar jadi 'belum_bayar' jika ada salah satu denda yang diisi
    $status_bayar = ($denda_kerusakan > 0 || $denda_telat > 0) ? 'belum_bayar' : 'lunas';

    $update_p = "UPDATE peminjaman SET 
                 status_transaksi = 'selesai', 
                 denda = '$denda_telat',
                 denda_kerusakan = '$denda_kerusakan',
                 status_pembayaran = '$status_bayar',
                 tgl_kembali_asli = NOW() 
                 WHERE id = '$id_p'";

    $update_a = "UPDATE alat SET status = 'ready' WHERE id = '$id_alat'";

    if (mysqli_query($conn, $update_p) && mysqli_query($conn, $update_a)) {
        echo "<script>alert('Konfirmasi Berhasil! Status: " . strtoupper(str_replace('_', ' ', $status_bayar)) . "'); window.location='dashboard_admin.php';</script>";
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pengembalian | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: white; width: 90%; max-width: 500px; padding: 30px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 25px; }
        .header i { color: #EE2E24; font-size: 40px; }
        .info-peminjam { background: #f8f9fa; padding: 15px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid #EE2E24; }
        .label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 2px; }
        .value { font-size: 14px; color: #333; margin-bottom: 12px; font-weight: 500; }
        .laporan-siswa { background: #fff5f5; border: 1px dashed #feb2b2; padding: 10px; border-radius: 8px; color: #c53030; font-size: 13px; margin-top: 10px; line-height: 1.5; }
        
        form label { display: block; margin-top: 15px; font-size: 14px; font-weight: 600; color: #444; }
        input { width: 100%; padding: 12px; margin-top: 8px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; font-family: 'Poppins'; outline: none; transition: 0.3s; }
        input:focus { border-color: #EE2E24; box-shadow: 0 0 0 3px rgba(238, 46, 36, 0.1); }
        
        .btn-submit { width: 100%; background: #EE2E24; color: white; border: none; padding: 15px; border-radius: 12px; font-weight: 700; margin-top: 25px; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #c41e16; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(238, 46, 36, 0.3); }
        .btn-back { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #888; font-size: 13px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        <h2 style="margin: 10px 0 0;">Tagihan Denda</h2>
        <p style="color: #666; font-size: 14px;">Tentukan denda keterlambatan & kerusakan</p>
    </div>

    <div class="info-peminjam">
        <div class="label">Peminjam</div>
        <div class="value"><?= htmlspecialchars($data['username']); ?> (<?= htmlspecialchars($data['no_induk']); ?>)</div>
        
        <div class="label">Alat</div>
        <div class="value"><?= htmlspecialchars($data['nama_alat']); ?></div>

        <?php if(!empty($data['laporan_kerusakan'])): ?>
            <div class="laporan-siswa">
                <strong><i class="fa-solid fa-triangle-exclamation"></i> Laporan Siswa:</strong><br>
                "<?= htmlspecialchars($data['laporan_kerusakan']); ?>"
            </div>
        <?php endif; ?>
    </div>

    <form action="" method="POST">
        <label>Denda Kerusakan (IDR)</label>
        <input type="number" name="denda_kerusakan" value="0" min="0" placeholder="Contoh: 50000" required autofocus>
        
        <label>Denda Keterlambatan (IDR)</label>
        <input type="number" name="denda_telat" value="0" min="0" placeholder="Contoh: 10000" required>
        
        <small style="color: #999; display: block; margin-top: 10px;">*Denda ini akan muncul sebagai tagihan di akun siswa.</small>

        <button type="submit" name="simpan_konfirmasi" class="btn-submit" onclick="return confirm('Selesaikan transaksi dan tagihkan denda?')">
            SIMPAN & SELESAIKAN
        </button>
    </form>

    <a href="dashboard_admin.php" class="btn-back">Kembali ke Dashboard</a>
</div>

</body>
</html>