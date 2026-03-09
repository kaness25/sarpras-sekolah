<?php
session_start();
include 'config.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'petugas') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['id_a'])) {
    $id_p = $_GET['id'];
    $id_alat = $_GET['id_a'];
    $denda = isset($_GET['denda']) ? $_GET['denda'] : 0;

    // 1. Update status transaksi langsung ke 'kembali' (atau 'menunggu_kembali')
    // Kita set ke 'menunggu_kembali' agar admin tetap bisa melihat riwayatnya di tabel konfirmasi
    // Atau langsung 'kembali' jika ingin dianggap sudah selesai total.
    $query_transaksi = "UPDATE peminjaman SET 
                        status_transaksi = 'menunggu_kembali', 
                        denda = '$denda' 
                        WHERE id = '$id_p'";

    // 2. Kembalikan stok alat
    $query_stok = "UPDATE alat SET stok = stok + 1, status = 'ready' WHERE id = '$id_alat'";

    if (mysqli_query($conn, $query_transaksi) && mysqli_query($conn, $query_stok)) {
        // Berhasil, balik ke dashboard admin
        header("Location: dashboard_admin.php?status=forced");
    } else {
        echo "Gagal melakukan paksa kembali: " . mysqli_error($conn);
    }
} else {
    header("Location: dashboard_admin.php");
}
?>