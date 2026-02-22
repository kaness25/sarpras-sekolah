<?php
session_start();
include 'config.php';

// Proteksi Admin
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin') { 
    header("Location: index.php"); 
    exit(); 
}

// Pastikan ID peminjaman ada
if (!isset($_GET['id_p'])) {
    header("Location: dashboard_admin.php");
    exit();
}

$id_p = mysqli_real_escape_string($conn, $_GET['id_p']);

// Ambil info lengkap peminjaman
$query_data = mysqli_query($conn, "SELECT p.*, a.nama_alat, u.username 
        FROM peminjaman p 
        JOIN alat a ON p.alat_id = a.id 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = '$id_p'");
$data = mysqli_fetch_assoc($query_data);

// Jika data tidak ditemukan
if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='dashboard_admin.php';</script>";
    exit();
}

if (isset($_POST['proses_selesai'])) {
    $denda_admin = intval($_POST['denda_admin']);
    $id_alat = $data['alat_id'];
    $jumlah_pinjam = $data['jumlah'];
    $status_akhir_alat = $_POST['kondisi_alat']; // 'ready' atau 'rusak'

    // Mulai transaksi database agar stok dan status terupdate bersamaan
    mysqli_begin_transaction($conn);

    try {
        // 1. Update status peminjaman jadi 'selesai' (PENTING: Harus 'selesai' agar muncul di laporan)
        // Gunakan kolom tgl_kembali yang sesuai dengan query laporan kamu
        $update_p = mysqli_query($conn, "UPDATE peminjaman SET 
                    status_transaksi = 'selesai', 
                    denda_kerusakan = '$denda_admin',
                    tgl_kembali = NOW() 
                    WHERE id = '$id_p'");

        // 2. Kembalikan stok alat ke tabel alat
        $update_a = mysqli_query($conn, "UPDATE alat SET 
                    stok = stok + $jumlah_pinjam, 
                    status = '$status_akhir_alat' 
                    WHERE id = '$id_alat'");

        if ($update_p && $update_a) {
            mysqli_commit($conn);
            echo "<script>alert('Pengembalian Berhasil! Data sudah masuk ke Laporan.'); window.location='dashboard_admin.php';</script>";
        } else {
            throw new Exception("Gagal mengupdate database");
        }

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pengembalian | Sarpras</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f9; padding: 40px; display: flex; justify-content: center; }
        .card { background: white; width: 100%; max-width: 500px; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 25px; }
        .header h3 { margin: 0; color: #2d3436; }
        .info { background: #fff5f5; padding: 20px; border-radius: 15px; margin-bottom: 25px; border-left: 5px solid #EE2E24; }
        .info div { margin-bottom: 8px; font-size: 14px; }
        label { display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #636e72; margin-bottom: 8px; }
        input, select { width: 100%; padding: 12px; margin-bottom: 20px; border: 2px solid #eee; border-radius: 10px; box-sizing: border-box; outline: none; transition: 0.3s; }
        input:focus { border-color: #EE2E24; }
        .btn-submit { background: #27ae60; color: white; border: none; width: 100%; padding: 15px; border-radius: 12px; font-weight: bold; cursor: pointer; font-size: 16px; transition: 0.3s; }
        .btn-submit:hover { background: #219150; transform: translateY(-2px); }
        .btn-back { display: block; text-align: center; margin-top: 15px; color: #b2bec3; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>

<div class="card">
    <div class="header">
        <i class="fa-solid fa-box-open fa-3x" style="color: #EE2E24; margin-bottom: 10px;"></i>
        <h3>Finalisasi Pengembalian</h3>
    </div>
    
    <div class="info">
        <div><small>Nama Siswa:</small><br><b><?php echo htmlspecialchars($data['username']); ?></b></div>
        <div><small>Alat Praktik:</small><br><b><?php echo htmlspecialchars($data['nama_alat']); ?> (<?php echo $data['jumlah']; ?> Unit)</b></div>
        <div><small>Catatan Kondisi dari Siswa:</small><br>
            <i style="color: #d63031;">"<?php echo $data['laporan_kerusakan'] ? htmlspecialchars($data['laporan_kerusakan']) : 'Kondisi Baik'; ?>"</i>
        </div>
    </div>

    <form method="POST">
        <label>Denda Kerusakan (Isi 0 jika aman)</label>
        <input type="number" name="denda_admin" value="0" min="0" required>

        <label>Update Status Alat</label>
        <select name="kondisi_alat">
            <option value="ready">✅ Ready (Bisa Dipinjam Lagi)</option>
            <option value="rusak">❌ Rusak (Masuk Gudang/Perbaikan)</option>
        </select>

        <button type="submit" name="proses_selesai" class="btn-submit">Selesaikan & Masukkan Laporan</button>
        <a href="dashboard_admin.php" class="btn-back">Batal, Kembali ke Dashboard</a>
    </form>
</div>

</body>
</html>