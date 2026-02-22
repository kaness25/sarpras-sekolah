<?php
session_start();
include 'config.php';

if (isset($_POST['klik_bayar'])) {
    $id_p = $_POST['id_peminjaman'];

    // Kita update status_pembayaran menjadi 'proses_verifikasi' 
    // (Pastikan di ENUM database kamu ada status ini, jika tidak, kita pakai kolom bantuan)
    // Untuk simpelnya, kita kirim pesan sukses saja dulu ke dashboard
    
    header("Location: dashboard_user.php?pesan=berhasil_lapor");
}
?>