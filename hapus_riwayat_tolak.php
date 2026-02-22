<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Hapus baris peminjaman yang statusnya ditolak agar list bersih kembali
    $query = mysqli_query($conn, "DELETE FROM peminjaman WHERE id = '$id' AND user_id = '$user_id' AND status_transaksi = 'ditolak'");

    if ($query) {
        header("Location: dashboard_user.php");
    } else {
        echo "Gagal menghapus riwayat.";
    }
}
?>