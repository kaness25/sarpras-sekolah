<?php 
session_start();
include 'config.php'; 

// Proteksi: Hanya Superadmin yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') { 
    header("Location: index.php"); 
    exit(); 
}

/** 1. AMBIL DATA STATISTIK **/

// Total jenis alat yang ada di sekolah
$total_barang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alat"))['total'];

// --- PERBAIKAN: Menghitung jumlah TOTAL UNIT yang sedang dipinjam secara dinamis ---
// Menggunakan SUM(jumlah) agar jika satu orang pinjam 4 sapu, terhitung 4, bukan 1.
$res_dipinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM peminjaman WHERE status_transaksi = 'disetujui'"));
$total_dipinjam = ($res_dipinjam['total']) ? $res_dipinjam['total'] : 0;

// Menghitung antrean (Peminjaman baru 'proses' ATAU Verifikasi denda 'proses_verifikasi')
$res_menunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status_transaksi = 'proses' OR status_pembayaran = 'proses_verifikasi'"));
$total_menunggu = $res_menunggu['total'];

// Menghitung TOTAL UNIT alat rusak
$res_rusak = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(jumlah) as total 
    FROM peminjaman 
    WHERE status_transaksi != 'selesai' 
    AND (denda_kerusakan > 0 OR laporan_kerusakan IS NOT NULL)
"));
$total_rusak = $res_rusak['total'] ?? 0;

/** 2. AMBIL DAFTAR ALAT TERBARU **/
$query_alat = "SELECT * FROM alat ORDER BY id DESC LIMIT 10";
$daftar_alat = mysqli_query($conn, $query_alat);
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
            --white: #ffffff;
        }

        body { font-family: 'Poppins', sans-serif; background: var(--telkom-gray); margin: 0; display: flex; }

        /* Sidebar Styling */
        .sidebar { 
            width: 260px; background: var(--white); height: 100vh; position: fixed; 
            display: flex; flex-direction: column; box-shadow: 2px 0 15px rgba(0,0,0,0.05);
            box-sizing: border-box; z-index: 100;
        }
        
        .sidebar-header { padding: 40px 20px; text-align: center; }
        .sidebar-header img { width: 60px; margin-bottom: 15px; border-radius: 8px; }
        .sidebar-header h3 { color: var(--telkom-red); margin: 0; font-size: 18px; font-weight: 700; text-transform: uppercase; }

        .sidebar-menu { padding: 10px; list-style: none; flex-grow: 1; margin: 0; }
        .sidebar-menu li { margin-bottom: 5px; }
        .sidebar-menu a { 
            display: flex; align-items: center; padding: 15px 25px; text-decoration: none; 
            color: #636e72; font-size: 14px; font-weight: 500; transition: 0.3s;
        }

        .sidebar-menu a.active { 
            color: var(--telkom-red); background: #fff5f5; border-left: 4px solid var(--telkom-red); font-weight: 600; 
        }

        .sidebar-menu a i { margin-right: 15px; width: 20px; font-size: 18px; }

        .sidebar-footer { 
            padding: 20px; 
            border-top: 1px solid #f1f1f1; 
            background: var(--white);
            margin-top: auto; 
        }

        .btn-logout {
            display: flex; align-items: center; justify-content: center; width: 100%;
            padding: 12px; background: #fff5f5; color: var(--telkom-red); text-decoration: none;
            border-radius: 12px; font-weight: 700; font-size: 14px; transition: all 0.3s ease;
            box-sizing: border-box; border: 1px solid #ffebeb; gap: 10px;
        }

        .btn-logout:hover { 
            background: var(--telkom-red); color: white; transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(238, 46, 36, 0.2);
        }

        /* Main Content */
        .main-content { flex: 1; padding: 40px; margin-left: 260px; min-height: 100vh; }
        .welcome-text h2 { margin: 0 0 30px 0; font-size: 24px; color: var(--telkom-dark); font-weight: 700; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { padding: 25px; border-radius: 20px; color: white; position: relative; overflow: hidden; box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        .card h3 { font-size: 35px; margin: 5px 0; font-weight: 700; }
        .card span { font-size: 13px; font-weight: 400; text-transform: uppercase; letter-spacing: 1px; }
        .card i { position: absolute; right: -10px; bottom: -10px; font-size: 80px; opacity: 0.2; }

        .bg-telkom-blue { background: #005aab; }
        .bg-telkom-red { background: var(--telkom-red); }
        .bg-telkom-yellow { background: #ffb400; }
        .bg-telkom-dark { background: var(--telkom-dark); }

        /* Content Boxes */
        .content-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 25px; }
        .section-box { background: var(--white); padding: 30px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 25px; }
        .section-box h4 { margin-top: 0; font-size: 18px; color: var(--telkom-dark); display: flex; align-items: center; gap: 12px; border-bottom: 2px solid #f1f1f1; padding-bottom: 15px; margin-bottom: 20px; }

        .item-row { display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #f9f9f9; transition: 0.3s; }
        
        .item-img-container { 
            width: 55px; height: 55px; border-radius: 12px; 
            overflow: hidden; margin-right: 15px; border: 1px solid #f1f1f1;
            background: #f8f9fa;
        }
        .item-img-container img { width: 100%; height: 100%; object-fit: cover; transition: 0.4s; }
        .item-row:hover .item-img-container img { transform: scale(1.15); }

        .item-info b { display: block; font-size: 15px; color: var(--telkom-dark); text-transform: capitalize; }
        
        .status-text { font-weight: 700; font-size: 12px; }
        .text-ready { color: #00b894; }
        .text-borrowed { color: var(--telkom-red); }

        .info-card { background: #2d3436; color: white; padding: 20px; border-radius: 15px; font-size: 13px; line-height: 1.6; }
        
        .btn-manage { 
            display: flex; align-items: center; justify-content: center; gap: 10px; width: fit-content;
            padding: 12px 25px; margin: 25px auto 0; background: var(--telkom-red); 
            color: white; text-align: center; text-decoration: none; border-radius: 12px; 
            font-weight: 600; font-size: 14px; transition: all 0.3s ease; 
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <img src="images/SMK-TELKOM-MEDAN.png" alt="Logo">
            <h3>SARPRAS DIGITAL</h3>
            <p style="margin:0; font-size:11px; color:#b2bec3; letter-spacing: 2px;">SUPERADMIN</p>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard_superadmin.php" class="active">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard SA
                </a>
            </li>
            <li>
                <a href="user_manajemen.php">
                    <i class="fa-solid fa-users-gear"></i> Manajemen User
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="fa-solid fa-power-off"></i> 
                <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="welcome-text">
            <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['username']); ?> (Super Admin)</h2>
        </div>

        <div class="stats-grid">
            <div class="card bg-telkom-blue">
                <span>Total Alat</span>
                <h3><?= $total_barang ?></h3>
                <i class="fa-solid fa-box"></i>
            </div>
            <div class="card bg-telkom-red">
                <span>Alat Dipinjam</span>
                <h3><?= $total_dipinjam ?></h3>
                <i class="fa-solid fa-fire-flame-curved"></i>
            </div>
            <div class="card bg-telkom-yellow">
                <span>Menunggu Verifikasi</span>
                <h3><?= $total_menunggu ?></h3>
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="card bg-telkom-dark">
                <span>Total Alat Rusak</span>
                <h3><?= $total_rusak ?></h3>
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

        <div class="content-grid">
            <div class="section-box">
                <h4><i class="fa-solid fa-boxes-stacked"></i> Daftar Alat Sekolah</h4>
                <div class="item-list">
                    <?php if(mysqli_num_rows($daftar_alat) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($daftar_alat)): 
                            $foto_tampil = (!empty($row['foto']) && $row['foto'] != '-') ? $row['foto'] : 'default.png';
                        ?>
                        <div class="item-row">
                            <div class="item-img-container">
                                <img src="images/<?= $foto_tampil ?>" onerror="this.src='images/default.png'">
                            </div>
                            <div class="item-info">
                                <b><?= htmlspecialchars($row['nama_alat']); ?></b>
                                <small>Kategori: <?= htmlspecialchars($row['kategori']); ?> | Status: 
                                    <?php 
                                        $stok = $row['stok'] ?? 0; 
                                        if ($stok > 0) {
                                            $label_status = "READY";
                                            $class_status = "text-ready";
                                        } else {
                                            $label_status = "HABIS";
                                            $class_status = "text-borrowed";
                                        }
                                    ?>
                                    <span class="status-text <?= $class_status ?>">
                                        <?= $label_status ?> (Sisa: <?= $stok ?>)
                                    </span>
                                </small>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color:#999; text-align:center;">Data alat tidak ditemukan.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="section-box">
                    <h4><i class="fa-solid fa-circle-info"></i> Info Admin</h4>
                    <div class="info-card">
                        Anda sedang berada di panel kontrol utama. Gunakan menu <b>Manajemen User</b> untuk menambah atau menghapus akses guru dan siswa.
                    </div>
                    <a href="user_manajemen.php" class="btn-manage">
                        <i class="fa-solid fa-users-gear"></i> Kelola Database User
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>