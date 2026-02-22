<?php
include 'config.php';
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    // Gunakan mysqli_real_escape_string untuk keamanan dari SQL Injection
    $id_p = mysqli_real_escape_string($conn, $_GET['id']);
    
    /**
     * PERBAIKAN: 
     * Kita mengubah 'status_pembayaran' menjadi 'proses_verifikasi'.
     * Ini akan memicu kondisi IF di dashboard user untuk menampilkan 
     * teks "Menunggu Konfirmasi Admin..".
     */
    $sql = "UPDATE peminjaman SET 
            status_pembayaran = 'proses_verifikasi' 
            WHERE id = '$id_p' AND username = '{$_SESSION['username']}'";

    $query = mysqli_query($conn, $sql);

    if ($query) {
        echo "<script>
                alert('Konfirmasi terkirim! Silakan serahkan uang denda kepada petugas Sarpras.'); 
                window.location='dashboard_user.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal mengirim konfirmasi. Silakan coba lagi.'); 
                window.location='dashboard_user.php';
              </script>";
    }
} else {
    header("Location: dashboard_user.php");
}
?>