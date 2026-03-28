<?php 
session_start();
include 'config.php'; 

// Proteksi: Admin (Petugas) dan Superadmin boleh masuk
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'petugas' && $_SESSION['role'] != 'superadmin')) { 
    header("Location: index.php"); 
    exit(); 
}

// Ambil parameter Filter
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '';
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '';
$search    = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query Dasar
$query_str = "SELECT p.*, u.username, u.no_induk, a.nama_alat 
              FROM peminjaman p
              JOIN users u ON p.user_id = u.id 
              JOIN alat a ON p.alat_id = a.id 
              WHERE p.status_transaksi = 'selesai'";

// Filter Tanggal
if ($tgl_awal && $tgl_akhir) {
    $query_str .= " AND (p.tgl_kembali >= '$tgl_awal 00:00:00' AND p.tgl_kembali <= '$tgl_akhir 23:59:59')";
}

if ($search) {
    $query_str .= " AND (u.username LIKE '%$search%' OR a.nama_alat LIKE '%$search%')";
}

$query_str .= " ORDER BY p.id DESC";

$query_laporan = mysqli_query($conn, $query_str);
if (!$query_laporan) {
    die("Query Error: " . mysqli_error($conn));
}

$total_selesai = mysqli_num_rows($query_laporan);

$total_pendapatan_denda = 0;
while($row = mysqli_fetch_assoc($query_laporan)){
    $total_pendapatan_denda += ($row['denda'] + $row['denda_kerusakan']);
}
mysqli_data_seek($query_laporan, 0);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Sarpras | SMK Telkom Medan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --telkom-red: #EE2E24;
            --telkom-dark: #2d3436;
            --telkom-light: #f8f9fa;
            --success: #27ae60;
        }
        
        body { background: #f4f7f9; margin: 0; font-family: 'Poppins', sans-serif; display: flex; }

        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; width: 100%; }
        .main-content { padding: 30px; width: 100%; }
        .card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .filter-box { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; margin-bottom: 25px; background: var(--telkom-light); padding: 20px; border-radius: 15px; }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group label { font-size: 11px; font-weight: 700; color: #636e72; text-transform: uppercase; }
        .filter-group input { padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none; background: white; }
        
        .btn { padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; border: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-size: 13px; }
        .btn-filter { background: var(--telkom-red); color: white; }
        .btn-print { background: var(--telkom-dark); color: white; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { padding: 15px; color: #636e72; font-size: 11px; text-transform: uppercase; text-align: left; border-bottom: 2px solid #f1f1f1; }
        td { padding: 15px; border-bottom: 1px solid #f8f9fa; font-size: 13px; color: var(--telkom-dark); vertical-align: middle; }
        
        .stats-summary { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-item { background: white; padding: 20px; border-radius: 15px; flex: 1; border-left: 5px solid var(--telkom-red); }
        .stat-item span { font-size: 12px; color: #636e72; }
        .stat-item h2 { margin: 5px 0 0; font-size: 20px; }

        .badge-denda { font-size: 11px; padding: 6px 12px; border-radius: 8px; font-weight: bold; display: inline-block; }
        .denda-telat { background: #fff5f5; color: #e74c3c; border: 1px solid #feb2b2; }
        .denda-rusak { background: #fffaf0; color: #d68910; border: 1px solid #fbeec1; }
        .kondisi-label { font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 5px; }
        .text-baik { color: var(--success); }
        .text-rusak { color: var(--telkom-red); }

        .durasi-tag { font-size: 10px; color: #636e72; font-weight: 600; background: #eee; padding: 2px 6px; border-radius: 4px; margin-top: 4px; display: inline-block; }

        @media print {
            .sidebar, .filter-box, .btn, .top-bar { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
        }
    </style>
</head>
<body>
    
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="top-bar">
            <h3><i class="fa-solid fa-chart-line" style="color: var(--telkom-red);"></i> Laporan Aktivitas</h3>
            <button onclick="window.print()" class="btn btn-print">
                <i class="fa-solid fa-print"></i> Ekspor PDF
            </button>
        </div>

        <div class="stats-summary">
            <div class="stat-item">
                <span>Total Barang Kembali</span>
                <h2><?= $total_selesai ?> <small style="font-size: 12px; color: #b2bec3;">Barang</small></h2>
            </div>
            <div class="stat-item" style="border-left-color: var(--success);">
                <span>Kas Denda Terkumpul</span>
                <h2>Rp <?= number_format($total_pendapatan_denda, 0, ',', '.') ?></h2>
            </div>
            <div class="stat-item" style="border-left-color: var(--telkom-dark);">
                <span>Periode Laporan</span>
                <h2 style="font-size: 15px;">
                    <?= ($tgl_awal) ? date('d/m/y', strtotime($tgl_awal)) : 'Semua' ?> - 
                    <?= ($tgl_akhir) ? date('d/m/y', strtotime($tgl_akhir)) : 'Sekarang' ?>
                </h2>
            </div>
        </div>

        <div class="card">
            <form method="GET" class="filter-box">
                <div class="filter-group">
                    <label>Cari Siswa / Alat</label>
                    <input type="text" name="search" placeholder="Ketik nama..." value="<?= $search ?>">
                </div>
                <div class="filter-group">
                    <label>Dari Tanggal</label>
                    <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>">
                </div>
                <div class="filter-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>">
                </div>
                <button type="submit" class="btn btn-filter">
                    <i class="fa-solid fa-filter"></i> Terapkan
                </button>
                <a href="laporan.php" class="btn" style="background: #eee; color: #666;">Reset</a>
            </form>

            <table>
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>Siswa</th>
                        <th>Alat Praktik</th>
                        <th>Kondisi</th>
                        <th>Waktu Pinjam & Kembali</th>
                        <th>Denda Telat</th>
                        <th>Denda Fisik</th>
                        <th>Total Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query_laporan) > 0): $no=1; ?>
                        <?php while($l = mysqli_fetch_assoc($query_laporan)): 
                            $total_baris = $l['denda'] + $l['denda_kerusakan'];
                            
                            $durasi_hari = 0;
                            if (!empty($l['tgl_pinjam']) && !empty($l['tgl_kembali'])) {
                                $tgl1 = new DateTime($l['tgl_pinjam']);
                                $tgl2 = new DateTime($l['tgl_kembali']);
                                $jarak = $tgl1->diff($tgl2);
                                $durasi_hari = $jarak->days + 1; 
                            }
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <b><?= strtoupper($l['username'] ?? 'User') ?></b><br>
                                <small style="color: var(--telkom-red)"><?= $l['no_induk'] ?? '-' ?></small>
                            </td>
                            <td>
                                <b><?= $l['nama_alat'] ?? '-' ?></b><br>
                                <small>Jumlah: <?= $l['jumlah'] ?? 0 ?> Unit</small>
                            </td>
                            <td>
                                <?php if($l['denda_kerusakan'] > 0): ?>
                                    <div class="kondisi-label text-rusak">
                                        <i class="fa-solid fa-circle-xmark"></i> Rusak
                                    </div>
                                    <div style="font-size: 10px; color: #666; font-style: italic; margin-top: 4px; line-height: 1.2;">
                                        Ket: <?= $l['laporan_kerusakan'] ?? '-' ?>
                                    </div>
                                <?php else: ?>
                                    <div class="kondisi-label text-baik">
                                        <i class="fa-solid fa-circle-check"></i> Baik
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size: 11px;">
                                    <i class="fa-regular fa-calendar"></i> Pinjam: 
                                    <b><?= (!empty($l['tgl_pinjam'])) ? date('d/m/Y', strtotime($l['tgl_pinjam'])) : '-' ?></b>
                                    <br>
                                    <i class="fa-solid fa-check-circle" style="color: var(--success)"></i> Kembali: 
                                    <b><?= (!empty($l['tgl_kembali'])) ? date('d/m/Y', strtotime($l['tgl_kembali'])) : '-' ?></b>
                                    <br>
                                    <div class="durasi-tag">Durasi: <?= $durasi_hari ?> Hari</div>

                                    <?php if($l['denda'] > 0): 
                                        $hari_telat = ceil($l['denda'] / 5000); 
                                    ?>
                                        <div style="font-size: 10px; color: var(--telkom-red); font-weight: 700; margin-top: 2px;">
                                            <i class="fa-solid fa-clock"></i> Telat: <?= $hari_telat ?> Hari
                                        </div>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php if($l['denda'] > 0): ?>
                                    <span class="badge-denda denda-telat">Rp <?= number_format($l['denda'], 0, ',', '.') ?></span>
                                <?php else: ?>
                                    <span style="color: #ccc;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($l['denda_kerusakan'] > 0): ?>
                                    <span class="badge-denda denda-rusak">Rp <?= number_format($l['denda_kerusakan'], 0, ',', '.') ?></span>
                                <?php else: ?>
                                    <span style="color: #ccc;">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 700; color: <?= ($total_baris > 0) ? 'var(--telkom-red)' : 'var(--success)' ?>">
                                Rp <?= number_format($total_baris, 0, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" align="center" style="padding: 40px; color: #999;">
                                <i class="fa-solid fa-box-open fa-2x"></i><br>Data tidak ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>