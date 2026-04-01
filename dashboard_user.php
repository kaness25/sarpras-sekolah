<?php 
session_start();
include 'config.php'; 

// Proteksi Halaman
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$user_id  = $_SESSION['user_id'];

// Proses Konfirmasi Bayar
if (isset($_POST['konfirmasi_bayar'])) {
    $id_peminjaman = mysqli_real_escape_string($conn, $_POST['id_peminjaman']);
    mysqli_query($conn, "UPDATE peminjaman SET status_pembayaran = 'proses_verifikasi' WHERE id = '$id_peminjaman'");
    header("Location: dashboard_user.php?status=verifying");
    exit();
}

// Ambil Alat yang stoknya > 0
$query_alat = mysqli_query($conn, "SELECT * FROM alat WHERE status = 'ready' AND stok > 0");

// Ambil Riwayat
$query_saya = mysqli_query($conn, "SELECT p.*, a.nama_alat, a.foto 
              FROM peminjaman p 
              LEFT JOIN alat a ON p.alat_id = a.id 
              WHERE p.user_id = '$user_id' 
              AND (
                  p.status_transaksi IN ('proses', 'disetujui', 'menunggu_kembali', 'ditolak') 
                  OR 
                  (p.status_transaksi = 'selesai' AND p.status_pembayaran != 'lunas')
              )
              ORDER BY p.id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Dashboard | Sarpras Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --telkom-red: #EE2E24;
            --telkom-dark: #2d3436;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #eb4d4b;
            --light: #f8f9fa;
        }
        body { background: var(--light); margin: 0; font-family: 'Poppins', sans-serif; color: var(--telkom-dark); }
        .navbar { background: white; padding: 12px 6%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 20px rgba(0,0,0,0.04); position: sticky; top: 0; z-index: 1000; }
        .navbar .brand { display: flex; align-items: center; gap: 12px; }
        .navbar .brand img { width: 40px; }
        .navbar .brand h2 { font-size: 20px; margin: 0; color: var(--telkom-red); font-weight: 800; letter-spacing: -0.5px; }
        .user-profile { display: flex; align-items: center; gap: 15px; background: #f0f2f5; padding: 5px 15px; border-radius: 50px; }
        .logout-btn { display: flex; align-items: center; gap: 8px; background: var(--telkom-red); color: white !important; padding: 6px 15px; border-radius: 50px; font-size: 12px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 25px; }
        .grid-layout { display: grid; grid-template-columns: 1.2fr 1.2fr; gap: 30px; }
        .card { background: white; border-radius: 24px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.06); margin-bottom: 20px; }
        .card-title { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; font-weight: 700; font-size: 1.2rem; }
        .item-row { display: flex; align-items: center; gap: 15px; padding: 15px; background: white; border-radius: 18px; margin-bottom: 15px; transition: 0.3s; border: 1px solid #f0f0f0; }
        .img-alat { width: 85px; height: 85px; border-radius: 14px; object-fit: cover; background: #eee; }
        .info-alat { flex: 1; }
        .qty-wrapper { display: flex; align-items: center; gap: 10px; background: #f8f9fa; padding: 5px 10px; border-radius: 12px; border: 1px solid #eee; }
        .input-qty { width: 45px; border: none; background: transparent; text-align: center; font-weight: 700; outline: none; }
        .btn { padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.3s; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn-pinjam { background: var(--telkom-red); color: white; }
        .badge { padding: 6px 14px; border-radius: 10px; font-size: 11px; font-weight: 600; }
        .bg-wait { background: #fff4e6; color: #fd7e14; }
        .bg-success { background: #ebfbee; color: #2f9e44; }
        .bg-return { background: #e7f5ff; color: #1971c2; }
        .bg-danger { background: #fff5f5; color: #e03131; }
        
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
        .loading-text { animation: pulse 1.5s infinite; color: var(--warning); font-weight: 600; font-size: 12px; display: flex; align-items: center; gap: 5px; justify-content: center; width: 100%; padding: 8px; border: 1px dashed var(--warning); border-radius: 10px; }

        @media (max-width: 992px) { .grid-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="brand">
            <img src="images/tel.jpeg" alt="Logo">
            <h2>SARPRAS DIGITAL</h2>
        </div>
        <div class="user-profile">
            <span style="font-size: 13px;">Halo, <b><?= htmlspecialchars($username); ?></b></span>
            <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin ingin keluar?')">
                <i class="fa-solid fa-power-off"></i><span>Logout</span>
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="grid-layout">
            
            <div class="card">
                <div class="card-title"><i class="fa-solid fa-wand-magic-sparkles" style="color:var(--telkom-red)"></i> Alat Tersedia</div>
                <?php while($row = mysqli_fetch_assoc($query_alat)): 
                    $foto = (!empty($row['foto']) && $row['foto'] != '-') ? $row['foto'] : 'default.png'; ?>
                    <div class="item-row">
                        <img src="images/<?= $foto ?>" class="img-alat" onerror="this.src='images/default.png'">
                        <div class="info-alat">
                            <strong><?= $row['nama_alat']; ?></strong>
                            <small><i class="fa-solid fa-square-check"></i> Stok: <?= $row['stok']; ?></small>
                        </div>
                        <form action="pinjam_proses.php" method="POST" style="display:flex; flex-direction:column; gap:5px;">
                            <div class="qty-wrapper">
                                <input type="number" name="jumlah" value="1" min="1" max="<?= $row['stok']; ?>" class="input-qty">
                            </div>
                            <input type="hidden" name="id_alat" value="<?= $row['id']; ?>">
                            <button type="submit" name="submit_pinjam" class="btn btn-pinjam">Pinjam</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="card">
                <div class="card-title"><i class="fa-solid fa-receipt" style="color:var(--warning)"></i> Status Peminjaman</div>
                
                <?php if(mysqli_num_rows($query_saya) > 0): ?>
                    <?php while($s = mysqli_fetch_assoc($query_saya)): 
                        $st = $s['status_transaksi'];
                        $sp = $s['status_pembayaran'];
                        
                        $estimasi_denda = 0;
                        $hari_telat = 0;
                        $deadline_tampil = "-";

                        if (!empty($s['tgl_kembali'])) {
                            $tgl_deadline_timestamp = strtotime($s['tgl_kembali']); 
                            $deadline_tampil = date('d M Y', $tgl_deadline_timestamp);
                            $now = time();

                            if ($st == 'disetujui' && $now > $tgl_deadline_timestamp) {
                                $selisih = $now - $tgl_deadline_timestamp;
                                $hari_telat = floor($selisih / (60 * 60 * 24));
                                $estimasi_denda = $hari_telat * 5000; 
                            }
                        }
                        
                        $denda_final = ($st == 'disetujui') ? $estimasi_denda : (($s['denda'] ?? 0) + ($s['denda_kerusakan'] ?? 0));
                    ?>
                        <div class="item-row" style="flex-direction: column; align-items: stretch; border-left: 5px solid <?= ($denda_final > 0) ? 'var(--danger)' : '#eee' ?>;">
                            <div style="display: flex; gap: 15px;">
                                <img src="images/<?= (!empty($s['foto']) ? $s['foto'] : 'default.png') ?>" style="width:70px; height:70px; border-radius:10px; object-fit:cover;" onerror="this.src='images/default.png'">
                                <div style="flex:1;">
                                    <strong><?= $s['nama_alat'] ?></strong>
                                    <div style="font-size:12px; color:#666;">Jumlah: <?= $s['jumlah'] ?> Unit</div>
                                    <?php if(!empty($s['tgl_kembali'])): ?>
                                        <div style="font-size:11px; color:<?= ($hari_telat > 0 && $st == 'disetujui') ? 'red' : '#777' ?>;">
                                            Batas Kembali: <?= $deadline_tampil ?> 
                                            <?= ($hari_telat > 0 && $st == 'disetujui') ? "($hari_telat Hari Terlambat)" : "" ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php 
                                        if($st == 'proses') echo '<span class="badge bg-wait">Pending</span>';
                                        elseif($st == 'disetujui') echo '<span class="badge bg-success">Dipinjam</span>';
                                        elseif($st == 'menunggu_kembali') echo '<span class="badge bg-return">Verifikasi...</span>';
                                        elseif($st == 'ditolak') echo '<span class="badge bg-danger">Ditolak</span>';
                                        elseif($st == 'selesai') {
                                            if ($sp != 'lunas' && $denda_final > 0) {
                                                echo '<span class="badge" style="background: #fff5f5; color: var(--danger); border: 1px solid var(--danger);">Menunggu Pembayaran</span>';
                                            } else {
                                                echo '<span class="badge bg-success">Selesai</span>';
                                            }
                                        }
                                    ?>
                                </div>
                            </div>

                            <?php if($denda_final > 0): ?>
                                <div style="margin-top:10px; padding:10px; background:#fff5f5; border-radius:10px; display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-size:11px; color:var(--danger); font-weight:600;">Tagihan Denda:</span>
                                    <b style="color:var(--danger);">Rp <?= number_format($denda_final, 0, ',', '.') ?></b>
                                </div>

                                <?php if($st == 'selesai'): ?>
                                    <div style="margin-top:5px;">
                                        <?php if($sp == 'proses_verifikasi'): ?>
                                            <div class="loading-text">
                                                <i class="fa-solid fa-clock-rotate-left"></i> Menunggu Konfirmasi Admin...
                                            </div>
                                        <?php elseif($sp == 'belum_bayar'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="id_peminjaman" value="<?= $s['id'] ?>">
                                                <button type="submit" name="konfirmasi_bayar" class="btn" style="width:100%; background:white; color:var(--danger); border:1px solid var(--danger); justify-content:center;" onclick="return confirm('Apakah Anda yakin sudah membayar tunai?')">
                                                    Konfirmasi Bayar Tunai
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div style="margin-top:10px; display:flex; justify-content:flex-end; gap:8px;">
                                <?php if($st == 'disetujui'): ?>
                                    <a href="laporkan_rusak.php?id_p=<?= $s['id'] ?>" class="btn" style="background: #f1f2f6; color: #555;">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Rusak
                                    </a>
                                    <a href="kembali_proses.php?id_p=<?= $s['id'] ?>&denda=<?= $estimasi_denda ?>" class="btn" style="background:var(--telkom-dark); color:white;" onclick="return confirm('Kembalikan alat sekarang?')">
                                        <i class="fa-solid fa-rotate-left"></i> Kembalikan
                                    </a>
                                <?php elseif($st == 'ditolak'): ?>
                                    <a href="oke_tolak.php?id_p=<?= $s['id'] ?>" class="btn" style="font-size:10px; background:#eee;">Hapus Notif</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align:center; padding:20px; color:#999;">Belum ada riwayat peminjaman.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</body>
</html>