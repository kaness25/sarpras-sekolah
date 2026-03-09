<?php
session_start();
include 'config.php';

// Proteksi: Pastikan hanya petugas yang bisa akses (sesuai role kamu)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'petugas') { 
    header("Location: index.php"); 
    exit(); 
}

if (isset($_GET['id'])) {
    $id_pinjam = mysqli_real_escape_string($conn, $_GET['id']);

    // 1. Ambil info alat, jumlah yang diajukan, dan stok real-time
    $query_info = mysqli_query($conn, "SELECT p.alat_id, p.jumlah, a.stok, a.nama_alat 
                                      FROM peminjaman p 
                                      JOIN alat a ON p.alat_id = a.id 
                                      WHERE p.id = '$id_pinjam'");
    $data = mysqli_fetch_assoc($query_info);
    
    if ($data) {
        $id_alat = $data['alat_id'];
        $jumlah_pinjam = $data['jumlah'];
        $stok_saat_ini = $data['stok'];

        // VALIDASI: Cek stok cukup sebelum dipotong
        if ($stok_saat_ini < $jumlah_pinjam) {
            echo "<script>
                    alert('Gagal! Stok " . $data['nama_alat'] . " tidak cukup untuk disetujui.');
                    window.location.href='dashboard_admin.php';
                  </script>";
            exit();
        }

        // Tentukan tanggal pinjam (sekarang) dan deadline (misal 3 hari ke depan)
        $tgl_pinjam = date('Y-m-d H:i:s'); 
        $tgl_kembali = date('Y-m-d H:i:s', strtotime('+3 days')); 

        // Mulai Transaksi Database untuk keamanan data
        mysqli_begin_transaction($conn);

        try {
            // 2. Update status transaksi menjadi 'disetujui'
            $update_transaksi = mysqli_query($conn, "UPDATE peminjaman SET 
                                status_transaksi = 'disetujui', 
                                tgl_pinjam = '$tgl_pinjam',
                                tgl_kembali = '$tgl_kembali' 
                                WHERE id = '$id_pinjam'");

            // 3. Potong stok alat di tabel 'alat'
            $update_stok = mysqli_query($conn, "UPDATE alat SET stok = stok - $jumlah_pinjam WHERE id = '$id_alat'");

            // 4. Update status alat menjadi 'dipinjam' jika stok habis (Sesuai enum di gambar database)
            mysqli_query($conn, "UPDATE alat SET status = 'dipinjam' WHERE id = '$id_alat' AND stok <= 0");

            if ($update_transaksi && $update_stok) {
                mysqli_commit($conn);
                header("Location: dashboard_admin.php?pesan=berhasil_setuju");
                exit();
            } else {
                throw new Exception("Gagal memperbarui data.");
            }

        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "<script>alert('Terjadi kesalahan sistem: " . $e->getMessage() . "'); window.location.href='dashboard_admin.php';</script>";
        }

    } else {
        echo "<script>alert('Data peminjaman tidak ditemukan.'); window.location.href='dashboard_admin.php';</script>";
    }
} else {
    header("Location: dashboard_admin.php");
}
?>