<?php
session_start();
include 'config.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // 1. Ambil waktu sekarang (Waktu saat admin klik Sahkan Lunas)
    // Ini yang akan mengisi kolom kosong di laporan Anda
    $tgl_sekarang = date('Y-m-d H:i:s');

    // 2. Update status_pembayaran, status_transaksi, DAN tgl_kembali
    $query = "UPDATE peminjaman SET 
              status_pembayaran = 'lunas', 
              status_transaksi = 'selesai', 
              tgl_kembali = '$tgl_sekarang' 
              WHERE id = '$id'";

    $update = mysqli_query($conn, $query);

    if ($update) {
        // Jika berhasil, balikkan ke dashboard admin dengan pesan sukses
        header("Location: dashboard_admin.php?pesan=lunas_berhasil");
        exit();
    } else {
        // Jika gagal karena error database
        echo "Gagal mengupdate database: " . mysqli_error($conn);
    }
} else {
    // Jika diakses tanpa ID, balikkan ke dashboard
    header("Location: dashboard_admin.php");
    exit();
}
?>