<?php
session_start();
include 'config.php';

// Cek apakah admin yang akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_peminjaman = $_GET['id'];

    // 1. Ambil ID alat dari tabel peminjaman sebelum statusnya diubah
    $query_cari = mysqli_query($conn, "SELECT alat_id FROM peminjaman WHERE id = '$id_peminjaman'");
    $data = mysqli_fetch_assoc($query_cari);
    $id_alat = $data['alat_id'];

    // 2. Update status transaksi di tabel peminjaman menjadi 'ditolak'
    $update_transaksi = mysqli_query($conn, "UPDATE peminjaman SET status_transaksi = 'ditolak' WHERE id = '$id_peminjaman'");

    // 3. KUNCI UTAMA: Update status alat di tabel alat menjadi 'ready' kembali
    $update_stok = mysqli_query($conn, "UPDATE alat SET status = 'ready' WHERE id = '$id_alat'");

    if ($update_transaksi && $update_stok) {
        echo "<script>
                alert('Peminjaman ditolak!');
                window.location='dashboard_admin.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal memproses data.');
                window.location='dashboard_admin.php';
              </script>";
    }
}
?>