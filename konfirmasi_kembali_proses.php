<?php 
session_start();
include 'config.php'; 

// 1. PROTEKSI: Izinkan Admin (Petugas) dan Superadmin
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) {
    header("Location: index.php");
    exit();
}

// 2. CEK DATA
if (isset($_GET['id_p']) && isset($_GET['id_a'])) {
    
    $id_p = mysqli_real_escape_string($conn, $_GET['id_p']); 
    $id_a = mysqli_real_escape_string($conn, $_GET['id_a']); 

    // --- LOGIKA PERHITUNGAN DENDA OTOMATIS ---
    
    // Ambil data tanggal pinjam untuk menghitung durasi
    $query_data = mysqli_query($conn, "SELECT tgl_pinjam FROM peminjaman WHERE id = '$id_p'");
    $data = mysqli_fetch_assoc($query_data);
    
    if ($data) {
        $tgl_pinjam = new DateTime($data['tgl_pinjam']);
        $tgl_sekarang = new DateTime(date('Y-m-d')); // Tanggal hari ini
        
        // Hitung selisih hari (Inklusif: +1 hari agar hari ini tetap dihitung)
        $interval = $tgl_pinjam->diff($tgl_sekarang);
        $total_hari = $interval->days + 1; 
        
        // Aturan: Jatah gratis 3 hari, selebihnya denda 5.000/hari
        $jatah_gratis = 3;
        $denda_akhir = 0;
        
        if ($total_hari > $jatah_gratis) {
            $hari_terlambat = $total_hari - $jatah_gratis;
            $denda_akhir = $hari_terlambat * 5000;
        }

        // 3. JALANKAN UPDATE
        // Update status_transaksi, denda, dan catat tgl_kembali secara real-time
        $tgl_kembali_sekarang = date('Y-m-d H:i:s');
        $q1 = mysqli_query($conn, "UPDATE peminjaman SET 
            status_transaksi = 'selesai', 
            tgl_kembali = '$tgl_kembali_sekarang', 
            denda = '$denda_akhir' 
            WHERE id = '$id_p'");

        // Update alat jadi 'Tersedia' kembali (pastikan kolom 'stok' di logika tambah/kurang juga sinkron jika perlu)
        // Di sini kita asumsikan status alat diubah kembali ke 'ready'
        $q2 = mysqli_query($conn, "UPDATE alat SET status='ready' WHERE id='$id_a'");

        if ($q1 && $q2) {
            // Arahkan kembali ke halaman laporan atau dashboard sesuai role
            $redirect = ($_SESSION['role'] == 'superadmin') ? 'laporan.php' : 'dashboard_admin.php';
            header("Location: $redirect?pesan=berhasil_kembali&denda=$denda_akhir");
        } else {
            header("Location: dashboard_admin.php?pesan=gagal_proses");
        }
    } else {
        header("Location: dashboard_admin.php?pesan=data_tidak_ditemukan");
    }
    exit();

} else {
    header("Location: dashboard_admin.php");
    exit();
}
?>