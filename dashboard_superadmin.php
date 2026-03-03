<?php 
session_start();
include 'config.php'; 

// Proteksi: Hanya Superadmin yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') { 
    header("Location: index.php"); 
    exit(); 
}

/** 1. AMBIL DATA STATISTIK **/
$total_barang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alat"))['total'];

$res_dipinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM peminjaman WHERE status_transaksi = 'disetujui'"));
$total_dipinjam = ($res_dipinjam['total']) ? $res_dipinjam['total'] : 0;

$res_menunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status_transaksi = 'proses' OR status_pembayaran = 'proses_verifikasi'"));
$total_menunggu = $res_menunggu['total'];

$res_rusak = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM peminjaman WHERE status_transaksi != 'selesai' AND (denda_kerusakan > 0 OR laporan_kerusakan IS NOT NULL)"));
$total_rusak = $res_rusak['total'] ?? 0;

$q_denda = mysqli_query($conn, "SELECT SUM(denda + denda_kerusakan) as total FROM peminjaman WHERE status_transaksi = 'selesai'");
$data_denda = mysqli_fetch_assoc($q_denda);
$total_duit = $data_denda['total'] ?? 0;

/** 2. AMBIL DAFTAR ALAT TERBARU **/
$sql_alat = "SELECT * FROM alat ORDER BY id DESC LIMIT 10";
$daftar_alat = mysqli_query($conn, $sql_alat);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Superadmin Dashboard | Sarpras Telkom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --telkom-red: #EE2E24;
            --telkom-dark: #2d3436;
            --telkom-gray: #f4f7f6;
            --success: #27ae60;
        }

        body { font-family: 'Poppins', sans-serif; background: var(--telkom-gray); margin: 0; display: flex; }

        /* Area Konten Utama */
        .main-content { flex: 1; padding: 40px; } /* Margin-left sudah diatur otomatis oleh sidebar.php */

        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 30px; }
        .card-stat { padding: 20px; border-radius: 15px; color: white; position: relative; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .card-stat h3 { font-size: 22px; margin: 5px 0; }
        .card-stat span { font-size: 10px; text-transform: uppercase; font-weight: 600; opacity: 0.9; }
        
        .bg-blue { background: #005aab; }
        .bg-red { background: var(--telkom-red); }
        .bg-green { background: var(--success); }
        .bg-yellow { background: #ffb400; }
        .bg-dark { background: var(--telkom-dark); }

        .content-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 25px; }
        .section-box { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
        .section-box h4 { margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        
        .item-row { display: flex; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f1f1; }
        .item-row:last-child { border-bottom: none; }
        .item-img { width: 50px; height: 50px; border-radius: 12px; margin-right: 15px; object-fit: cover; background: #f9f9f9; border: 1px solid #eee; }
        
        .status-ready { color: var(--success); font-weight: 700; }
        .status-empty { color: var(--telkom-red); font-weight: 700; }

        .btn-manage { 
            display: inline-block; padding: 10px 20px; background: var(--telkom-red); 
            color: white; text-decoration: none; border-radius: 10px; font-size: 13px; margin-top: 15px;
            transition: 0.3s; font-weight: 600;
        }
        .btn-manage:hover { opacity: 0.8; transform: translateY(-2px); }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h2 style="margin-bottom: 30px;">Selamat Datang, <?= htmlspecialchars($_SESSION['username']); ?> 👋</h2>

        <div class="stats-grid">
            <div class="card-stat bg-blue">
                <span>Total Jenis Alat</span>
                <h3><?= $total_barang ?></h3>
            </div>
            <div class="card-stat bg-red">
                <span>Alat Dipinjam</span>
                <h3><?= $total_dipinjam ?></h3>
            </div>
            <div class="card-stat bg-green">
                <span>Kas Denda</span>
                <h3>Rp <?= number_format($total_duit, 0, ',', '.') ?></h3>
            </div>
            <div class="card-stat bg-yellow">
                <span>Verifikasi</span>
                <h3><?= $total_menunggu ?></h3>
            </div>
            <div class="card-stat bg-dark">
                <span>Alat Rusak</span>
                <h3><?= $total_rusak ?></h3>
            </div>
        </div>

        <div class="content-grid">
            <div class="section-box">
                <h4><i class="fa-solid fa-boxes-stacked" style="color: var(--telkom-red);"></i> Daftar Alat Terbaru</h4>
                <?php if(mysqli_num_rows($daftar_alat) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($daftar_alat)): 
                        $foto = (!empty($row['foto']) && $row['foto'] != '-') ? $row['foto'] : 'default.png';
                    ?>
                    <div class="item-row">
                        <img src="images/<?= $foto ?>" class="item-img" onerror="this.src='images/default.png'">
                        <div>
                            <div style="font-weight: 600; font-size: 15px;"><?= $row['nama_alat'] ?></div>
                            <small style="color: #888;">
                                <?= $row['kategori'] ?> | 
                                <span class="<?= ($row['stok'] > 0) ? 'status-ready' : 'status-empty' ?>">
                                    <?= ($row['stok'] > 0) ? "READY ({$row['stok']})" : "HABIS" ?>
                                </span>
                            </small>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 20px;">Tidak ada data alat.</p>
                <?php endif; ?>
                <a href="stok_barang.php" class="btn-manage" style="background: var(--telkom-dark);">Lihat Semua Stok</a>
            </div>

            <div class="section-box" style="height: fit-content;">
                <h4><i class="fa-solid fa-circle-info" style="color: #ffb400;"></i> Info Panel</h4>
                <p style="font-size: 13px; color: #666; line-height: 1.6;">
                    Halo <b>Superadmin</b>, gunakan dashboard ini untuk memantau sirkulasi alat praktikum secara <i>real-time</i>. Anda memiliki akses penuh untuk:
                </p>
                <ul style="font-size: 13px; color: #666; padding-left: 20px;">
                    <li>Mengelola hak akses seluruh user.</li>
                    <li>Melihat laporan denda yang terkumpul.</li>
                    <li>Audit stok sarana prasarana.</li>
                </ul>
                <a href="user_manajemen.php" class="btn-manage">Kelola Database User</a>
            </div>
        </div>
    </div>

</body>
</html>