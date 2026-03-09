<?php 
session_start();
include 'config.php'; 

// PERBAIKAN PROTEKSI HALAMAN: Izinkan role 'admin' DAN 'petugas'
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'petugas')) {
    header("Location: index.php");
    exit();
}

// 1. Permintaan Pinjaman Baru (Proses)
$query_pinjam = mysqli_query($conn, "SELECT p.*, u.username, u.no_induk, a.nama_alat 
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN alat a ON p.alat_id = a.id 
    WHERE p.status_transaksi = 'proses' 
    ORDER BY p.id DESC");

// 2. Monitoring Peminjaman Aktif (Sedang Dipakai)
$query_aktif = mysqli_query($conn, "SELECT p.*, u.username, a.nama_alat 
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN alat a ON p.alat_id = a.id 
    WHERE p.status_transaksi = 'disetujui' 
    ORDER BY p.tgl_pinjam DESC");

// 3. Verifikasi Pengembalian (Menunggu Konfirmasi)
$query_konfirmasi_kembali = mysqli_query($conn, "SELECT p.*, u.username, a.nama_alat 
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN alat a ON p.alat_id = a.id 
    WHERE p.status_transaksi = 'menunggu_kembali' 
    ORDER BY p.id DESC");

// 4. Verifikasi Pembayaran Denda
$query_bayar = mysqli_query($conn, "SELECT p.*, u.username, a.nama_alat 
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN alat a ON p.alat_id = a.id 
    WHERE p.status_pembayaran = 'proses_verifikasi' 
    ORDER BY p.id DESC");

// Hitung Statistik Singkat
$count_pinjam = mysqli_num_rows($query_pinjam);
$count_aktif = mysqli_num_rows($query_aktif);
$count_verif = mysqli_num_rows($query_konfirmasi_kembali);

// Ambil sapaan berdasarkan Role
$display_role = ($_SESSION['role'] == 'petugas') ? 'Petugas' : 'Admin';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard <?= $display_role ?> | Sarpras Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --telkom-red: #EE2E24;
            --telkom-dark: #2d3436;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --primary: #3498db;
            --bg-light: #f8f9fa;
        }

        body { font-family: 'Poppins', sans-serif; background: var(--bg-light); margin: 0; color: var(--telkom-dark); }
        .main-content { margin-left: 260px; padding: 40px; transition: 0.3s; }
        
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: white; padding: 20px; border-radius: 15px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-bottom: 4px solid #ddd; }
        .stat-box i { font-size: 25px; padding: 15px; border-radius: 12px; }
        .stat-info h4 { margin: 0; font-size: 13px; color: #888; }
        .stat-info h2 { margin: 0; font-size: 22px; }

        .card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 30px; border: none; overflow: hidden; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h3 { margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; background: #fdfdfd; font-size: 12px; color: #a0aec0; text-transform: uppercase; border-bottom: 1px solid #eee; }
        td { padding: 18px 15px; border-bottom: 1px solid #f8f9fa; font-size: 14px; }

        .btn-group { display: flex; gap: 8px; }
        .btn-action { text-decoration: none; font-size: 11px; font-weight: 600; padding: 8px 14px; border-radius: 10px; color: white; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer; }
        .btn-action:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        
        .btn-approve { background: var(--success); }
        .btn-reject { background: var(--danger); }
        .btn-return { background: var(--warning); color: #000; }
        .btn-force { background: var(--telkom-dark); }
        .btn-lunas { background: var(--primary); }

        .badge-qty { background: #ebf8ff; color: #3182ce; padding: 4px 10px; border-radius: 8px; font-weight: bold; }
        .report-box { background: #fff5f5; border-left: 4px solid var(--danger); padding: 10px; border-radius: 5px; margin-top: 10px; font-size: 12px; }
        
        .alert-notif { position: fixed; top: 20px; right: 20px; z-index: 1000; padding: 15px 25px; border-radius: 12px; color: white; display: flex; align-items: center; gap: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); animation: slideIn 0.5s ease; }
        @keyframes slideIn { from { transform: translateX(100%); } to { transform: translateX(0); } }

        @media (max-width: 992px) { .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <?php if (isset($_GET['pesan'])): ?>
            <div class="alert-notif" style="background: <?= $_GET['pesan'] == 'berhasil_kembali' ? 'var(--success)' : 'var(--primary)' ?>;">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <strong>Sistem Informasi</strong><br>
                    <small><?= $_GET['pesan'] == 'berhasil_kembali' ? 'Pengembalian barang berhasil dikonfirmasi.' : 'Pembayaran denda telah disahkan.' ?></small>
                </div>
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <div>
                <h1 style="margin:0; font-weight: 700;">Halo, <?= $display_role ?></h1>
                <p style="color: #718096; margin-top: 5px;">Kelola alur peminjaman prasarana sekolah secara real-time.</p>
            </div>
            <div style="text-align: right;">
                <div style="font-weight: 600; font-size: 16px;"><i class="fa-solid fa-calendar-day" style="color:var(--telkom-red)"></i> <?= date('d F Y'); ?></div>
                <small id="clock" style="color: #a0aec0;"></small>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-box" style="border-color: var(--primary);">
                <i class="fa-solid fa-file-invoice" style="background: #e1f5fe; color: var(--primary);"></i>
                <div class="stat-info"><h4>Antrian</h4><h2><?= $count_pinjam ?></h2></div>
            </div>
            <div class="stat-box" style="border-color: var(--warning);">
                <i class="fa-solid fa-hand-holding" style="background: #fff9db; color: var(--warning);"></i>
                <div class="stat-info"><h4>Dipinjam</h4><h2><?= $count_aktif ?></h2></div>
            </div>
            <div class="stat-box" style="border-color: var(--success);">
                <i class="fa-solid fa-box-open" style="background: #ebfbee; color: var(--success);"></i>
                <div class="stat-info"><h4>Perlu Verif</h4><h2><?= $count_verif ?></h2></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-bell-concierge" style="color:var(--primary)"></i> Permintaan Baru</h3>
            </div>
            <table>
                <thead>
                    <tr><th>Siswa</th><th>Barang</th><th>Jumlah</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if($count_pinjam > 0): while($p = mysqli_fetch_assoc($query_pinjam)): ?>
                    <tr>
                        <td><strong><?= $p['username']; ?></strong><br><small><?= $p['no_induk']; ?></small></td>
                        <td><span style="font-weight:500"><?= $p['nama_alat']; ?></span></td>
                        <td><span class="badge-qty"><?= $p['jumlah']; ?> Unit</span></td>
                        <td>
                            <div class="btn-group">
                                <a href="setujui_pinjam.php?id=<?= $p['id']; ?>&id_a=<?= $p['alat_id']; ?>" class="btn-action btn-approve"><i class="fa-solid fa-check"></i> Setujui</a>
                                <a href="tolak_pinjam.php?id=<?= $p['id']; ?>" class="btn-action btn-reject" onclick="return confirm('Tolak pinjaman ini?')"><i class="fa-solid fa-xmark"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="4" style="text-align:center; color:#ccc; padding:30px;">Tidak ada permintaan masuk.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-clock-rotate-left" style="color:var(--warning)"></i> Peminjaman Sedang Berlangsung</h3>
            </div>
            <table>
                <thead>
                    <tr><th>Peminjam</th><th>Alat</th><th>Deadline Kembali</th><th>Status / Denda</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if($count_aktif > 0): while($a = mysqli_fetch_assoc($query_aktif)): 
                        $tgl_deadline = strtotime($a['tgl_kembali']); 
                        $now = time();
                        $hari_telat = 0;
                        $denda_berjalan = 0;

                        if ($now > $tgl_deadline) {
                            $selisih = $now - $tgl_deadline;
                            $hari_telat = floor($selisih / (60 * 60 * 24));
                            $denda_berjalan = $hari_telat * 5000; 
                        }
                    ?>
                    <tr>
                        <td><strong><?= strtoupper($a['username']); ?></strong></td>
                        <td><?= $a['nama_alat']; ?> <small>(<?= $a['jumlah']; ?> Unit)</small></td>
                        <td><?= date('d M Y', $tgl_deadline); ?></td>
                        <td>
                            <?php if($hari_telat > 0): ?>
                                <span style="color:var(--danger); font-weight:bold; font-size:12px;">
                                    ⚠️ TELAT <?= $hari_telat ?> HARI (Rp <?= number_format($denda_berjalan,0,',','.') ?>)
                                </span>
                            <?php else: ?>
                                <span style="color:var(--success); font-size:12px;">
                                    <i class="fa-solid fa-circle-check"></i> Aman (Sesuai deadline)
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><a href="paksa_kembali.php?id=<?= $a['id']; ?>&id_a=<?= $a['alat_id']; ?>&denda=<?= $denda_berjalan; ?>" class="btn-action btn-force" onclick="return confirm('Paksa kembalikan alat?')">Paksa</a></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" style="text-align:center; color:#ccc; padding:30px;">Gudang kosong, tidak ada alat keluar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px;">
            <div class="card">
                <h3><i class="fa-solid fa-box-open" style="color:var(--success)"></i> Verifikasi Pengembalian</h3>
                <table>
                    <thead>
                        <tr><th>Siswa</th><th>Alat & Laporan</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if($count_verif > 0): while($k = mysqli_fetch_assoc($query_konfirmasi_kembali)): ?>
                        <tr>
                            <td><strong><?= $k['username']; ?></strong></td>
                            <td>
                                <?= $k['nama_alat']; ?> (<?= $k['jumlah']; ?>)
                                <?php if(!empty($k['laporan_kerusakan'])): ?>
                                    <div class="report-box">"<?= $k['laporan_kerusakan']; ?>"</div>
                                <?php endif; ?>
                            </td>
                            <td><a href="konfirmasi_kembali_form.php?id_p=<?= $k['id']; ?>" class="btn-action btn-return">Periksa</a></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="3" style="text-align:center; color:#ccc; padding:20px;">Menunggu barang kembali...</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card" style="border-top: 4px solid var(--primary);">
                <h3><i class="fa-solid fa-receipt" style="color:var(--primary)"></i> Denda Masuk</h3>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if(mysqli_num_rows($query_bayar) > 0): while($b = mysqli_fetch_assoc($query_bayar)): 
                        $total = ($b['denda'] ?? 0) + ($b['denda_kerusakan'] ?? 0);
                    ?>
                    <div style="background:#fcfcfc; padding:15px; border-radius:12px; margin-bottom:10px; border:1px solid #eee;">
                        <div style="display:flex; justify-content:space-between;">
                            <strong><?= $b['username']; ?></strong>
                            <b style="color:var(--danger)">Rp<?= number_format($total,0,',','.') ?></b>
                        </div>
                        <p style="font-size:11px; color:#888; margin:5px 0;"><?= $b['nama_alat'] ?></p>
                        <a href="konfirmasi_lunas.php?id=<?= $b['id']; ?>" class="btn-action btn-lunas" style="width:100%; justify-content:center; margin-top:10px;">SAHKAN LUNAS</a>
                    </div>
                    <?php endwhile; else: ?>
                    <p style="text-align:center; color:#ccc; font-style:italic;">Tidak ada antrian denda.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID') + " WIB";
        }
        setInterval(updateClock, 1000);

        setTimeout(() => {
            const alert = document.querySelector('.alert-notif');
            if(alert) {
                alert.style.transition = "0.5s";
                alert.style.opacity = "0";
                alert.style.transform = "translateX(100%)";
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>