<?php
session_start();
include 'config.php';

// Proteksi: Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
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

        // VALIDASI: Cek sekali lagi apakah stok cukup sebelum dipotong
        if ($stok_saat_ini < $jumlah_pinjam) {
            echo "<script>
                    alert('Gagal! Stok " . $data['nama_alat'] . " tidak cukup untuk disetujui.');
                    window.location.href='dashboard_admin.php';
                  </script>";
            exit();
        }

        // --- PERBAIKAN DI SINI ---
        $tgl_pinjam = date('Y-m-d H:i:s'); 
        // Menentukan deadline otomatis, misal 3 hari dari sekarang agar tgl_kembali tidak kosong
        $tgl_kembali = date('Y-m-d H:i:s', strtotime('+3 days')); 

        // Mulai Transaksi Database
        mysqli_begin_transaction($conn);

        try {
            // 2. Update status transaksi menjadi 'disetujui', isi tgl_pinjam DAN tgl_kembali
            $update_transaksi = mysqli_query($conn, "UPDATE peminjaman SET 
                                status_transaksi = 'disetujui', 
                                tgl_pinjam = '$tgl_pinjam',
                                tgl_kembali = '$tgl_kembali' 
                                WHERE id = '$id_pinjam'");

            // 3. Potong stok alat
            $update_stok = mysqli_query($conn, "UPDATE alat SET stok = stok - $jumlah_pinjam WHERE id = '$id_alat'");

            // 4. Update status alat jika stok menjadi 0
            mysqli_query($conn, "UPDATE alat SET status = 'dipinjam' WHERE id = '$id_alat' AND stok <= 0");

            if ($update_transaksi && $update_stok) {
                mysqli_commit($conn);
                header("Location: dashboard_admin.php?status=disetujui");
                exit();
            } else {
                throw new Exception("Query gagal dieksekusi.");
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