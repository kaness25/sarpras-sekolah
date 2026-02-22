<?php
session_start();
include 'config.php';

// Proteksi: Pastikan user login dan parameter ID tersedia
if (!isset($_SESSION['user_id']) || !isset($_GET['id_p'])) {
    header("Location: dashboard_user.php");
    exit();
}

$id_peminjaman = mysqli_real_escape_string($conn, $_GET['id_p']);
$user_id = $_SESSION['user_id'];

/* LOGIKA:
   Karena stok belum dipotong saat user klik 'Pinjam', 
   maka saat admin 'Menolak', stok di gudang masih utuh.
   Jadi di sini kita CUKUP MENGHAPUS data peminjaman saja.
*/

// Hapus riwayat peminjaman yang statusnya ditolak milik user yang sedang login
$delete_peminjaman = mysqli_query($conn, "DELETE FROM peminjaman 
                                          WHERE id = '$id_peminjaman' 
                                          AND user_id = '$user_id' 
                                          AND status_transaksi = 'ditolak'");

if ($delete_peminjaman && mysqli_affected_rows($conn) > 0) {
    echo "<script>
            alert('Notifikasi penolakan telah dihapus.');
            window.location='dashboard_user.php';
          </script>";
} else {
    // Jika ID tidak ditemukan atau status bukan ditolak
    echo "<script>
            alert('Gagal menghapus notifikasi atau data tidak ditemukan.');
            window.location='dashboard_user.php';
          </script>";
}
?>