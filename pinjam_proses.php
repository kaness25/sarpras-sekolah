<?php 
session_start();
include 'config.php'; 

// Proteksi: Pastikan hanya user login yang bisa akses
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['submit_pinjam'])) {
    // Gunakan mysqli_real_escape_string dan intval untuk keamanan data
    $id_alat       = mysqli_real_escape_string($conn, $_POST['id_alat']);
    $jumlah_pinjam = intval($_POST['jumlah']); 
    $user_id       = $_SESSION['user_id'];

    // 1. Validasi Input: Pastikan jumlah tidak 0 atau negatif
    if ($jumlah_pinjam <= 0) {
        echo "<script>alert('Jumlah pinjam tidak valid!'); window.location.href='dashboard_user.php';</script>";
        exit();
    }

    // 2. Cek ketersediaan stok di database
    $cek_stok  = mysqli_query($conn, "SELECT stok, nama_alat FROM alat WHERE id = '$id_alat'");
    $data_alat = mysqli_fetch_assoc($cek_stok);

    if ($data_alat && $data_alat['stok'] >= $jumlah_pinjam) {
        
        /* LOGIKA BARU:
           - Status diset ke 'proses'.
           - tgl_pinjam tetap NULL karena baru diisi saat barang benar-benar diambil (Admin klik Setuju).
           - STOK TIDAK BERKURANG DI SINI.
        */
        $query_pinjam = "INSERT INTO peminjaman (user_id, alat_id, jumlah, status_transaksi, status_pembayaran) 
                         VALUES ('$user_id', '$id_alat', '$jumlah_pinjam', 'proses', 'belum_bayar')";

        if (mysqli_query($conn, $query_pinjam)) {
            echo "<script>
                    alert('Permintaan pinjam " . htmlspecialchars($data_alat['nama_alat']) . " berhasil dikirim! Silakan tunggu konfirmasi petugas.');
                    window.location.href='dashboard_user.php';
                  </script>";
        } else {
            // Error handling jika query gagal
            error_log("Gagal Insert Peminjaman: " . mysqli_error($conn));
            echo "<script>alert('Terjadi kesalahan sistem. Silakan coba lagi.'); window.location.href='dashboard_user.php';</script>";
        }

    } else {
        // Jika stok fisik sudah habis atau kurang dari permintaan
        echo "<script>
                alert('Maaf, stok " . htmlspecialchars($data_alat['nama_alat']) . " saat ini tidak mencukupi!');
                window.location.href='dashboard_user.php';
              </script>";
    }
} else {
    header("Location: dashboard_user.php");
    exit();
}
?>