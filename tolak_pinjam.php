<?php
session_start();
include 'config.php';

// Proteksi: Pastikan hanya petugas yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'petugas') { 
    header("Location: index.php"); 
    exit(); 
}

if (isset($_GET['id'])) {
    $id_pinjam = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Update status transaksi menjadi 'ditolak'
    $query = mysqli_query($conn, "UPDATE peminjaman SET status_transaksi = 'ditolak' WHERE id = '$id_pinjam'");

    if ($query) {
        // Kembali ke dashboard dengan notifikasi berhasil
        header("Location: dashboard_admin.php?pesan=berhasil_tolak");
        exit();
    } else {
        echo "<script>alert('Gagal menolak peminjaman.'); window.location.href='dashboard_admin.php';</script>";
    }
} else {
    header("Location: dashboard_admin.php");
}
?>