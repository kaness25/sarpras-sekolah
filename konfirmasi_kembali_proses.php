<?php 
session_start();
include 'config.php'; 

// 1. PROTEKSI
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// 2. CEK DATA
if (isset($_GET['id_p']) && isset($_GET['id_a'])) {
    
    $id_p = mysqli_real_escape_string($conn, $_GET['id_p']); 
    $id_a = mysqli_real_escape_string($conn, $_GET['id_a']); 

    // CATATAN: Jangan update tgl_kembali di sini jika tgl_kembali digunakan sebagai DEADLINE.
    // Jika kamu ingin mencatat kapan barang benar-benar diterima, sebaiknya gunakan kolom baru 
    // seperti 'tgl_realisasi_kembali'. 
    
    // Namun, jika ingin tetap sederhana:
    // 3. JALANKAN UPDATE
    // Kita hanya perlu mengubah status_transaksi menjadi 'selesai'
    $q1 = mysqli_query($conn, "UPDATE peminjaman SET status_transaksi='selesai' WHERE id='$id_p'");

    // Update alat jadi 'ready' kembali
    $q2 = mysqli_query($conn, "UPDATE alat SET status='ready' WHERE id='$id_a'");

    if ($q1 && $q2) {
        header("Location: dashboard_admin.php?pesan=berhasil_kembali");
    } else {
        header("Location: dashboard_admin.php?pesan=gagal_proses");
    }
    exit();

} else {
    header("Location: dashboard_admin.php");
    exit();
}
?>