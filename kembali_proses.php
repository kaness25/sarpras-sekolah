<?php
session_start();
include 'config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id_p'])) {
    header("Location: dashboard_user.php");
    exit();
}

$id_p = mysqli_real_escape_string($conn, $_GET['id_p']);

// 1. Ambil info jumlah yang dipinjam dan id_alat dari database
$query_info = mysqli_query($conn, "SELECT alat_id, jumlah FROM peminjaman WHERE id = '$id_p'");
$data = mysqli_fetch_assoc($query_info);

if ($data) {
    $id_alat = $data['alat_id'];
    $jumlah_pinjam = $data['jumlah']; 

    // --- LOGIKA MENANGKAP DENDA ---
    // Mengambil nilai denda dari URL (dikirim dari dashboard_user.php)
    $denda = isset($_GET['denda']) ? (int)$_GET['denda'] : 0;

    // 2. Update status transaksi & CATAT TANGGAL KEMBALI ASLI (HARI INI)
    // tgl_kembali diupdate menjadi NOW() agar di laporan muncul tanggal hari ini
    $query_transaksi = "UPDATE peminjaman SET 
                        status_transaksi = 'menunggu_kembali', 
                        denda = '$denda',
                        tgl_kembali = NOW() 
                        WHERE id = '$id_p'";

    // 3. Tambahkan kembali stok alat SESUAI JUMLAH yang dipinjam
    $query_stok = "UPDATE alat SET 
                   stok = stok + $jumlah_pinjam, 
                   status = 'ready' 
                   WHERE id = '$id_alat'";

    // Jalankan query
    if (mysqli_query($conn, $query_transaksi) && mysqli_query($conn, $query_stok)) {
        header("Location: dashboard_user.php?status=returned");
        exit();
    } else {
        echo "Error memproses pengembalian: " . mysqli_error($conn);
    }
} else {
    echo "Data peminjaman tidak ditemukan.";
}
?>