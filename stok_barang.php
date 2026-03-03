<?php 
session_start();
include 'config.php'; 

// 1. Proteksi Akses: Hanya Admin (Petugas) dan Superadmin yang boleh masuk
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')) { 
    header("Location: index.php"); 
    exit(); 
}

// 2. Ambil data alat dari database
$query_alat = mysqli_query($conn, "SELECT * FROM alat ORDER BY nama_alat ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris Barang | Sarpras Digital</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* CSS KHUSUS AREA KONTEN (Agar tidak bentrok dengan sidebar.php) */
        :root {
            --telkom-red: #EE2E24;
            --success: #27ae60;
            --danger: #eb2f06;
            --gray-sub: #b2bec3;
        }

        /* Styling Header Area */
        .header-flex { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
        }
        .header-title h2 { margin: 0; font-size: 26px; font-weight: 700; color: #2d3436; }
        .header-title p { margin: 5px 0 0; color: var(--gray-sub); font-size: 14px; }

        .btn-add { 
            background: var(--telkom-red); 
            color: white; 
            padding: 12px 25px; 
            border-radius: 12px; 
            text-decoration: none; 
            font-weight: 600; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(238, 46, 36, 0.2);
        }
        .btn-add:hover { transform: translateY(-2px); opacity: 0.9; }

        /* Styling Label Tabel (Header Kolom) */
        .table-labels {
            display: grid; 
            grid-template-columns: 80px 2fr 1.5fr 1fr 1fr 120px;
            padding: 0 30px 15px; 
            color: var(--gray-sub); 
            font-size: 12px; 
            font-weight: 700; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Styling List Barang */
        .inventory-list { display: flex; flex-direction: column; gap: 15px; }
        
        .inventory-card {
            background: white; 
            padding: 20px 30px; 
            border-radius: 20px;
            display: grid; 
            grid-template-columns: 80px 2fr 1.5fr 1fr 1fr 120px;
            align-items: center; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.02); 
            transition: 0.3s;
        }
        .inventory-card:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 25px rgba(0,0,0,0.05); 
        }

        /* Box Foto Produk */
        .img-box { 
            width: 60px; 
            height: 60px; 
            border-radius: 15px; 
            overflow: hidden; 
            border: 1px solid #f1f1f1; 
            background: #f8f9fa;
        }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }

        /* Teks & Badge */
        .item-name { font-weight: 600; font-size: 16px; color: #2d3436; }
        .category-info { color: #636e72; font-size: 14px; }
        .stok-info { font-weight: 700; font-size: 15px; }
        
        .badge { 
            padding: 6px 14px; 
            border-radius: 10px; 
            font-size: 11px; 
            font-weight: 700; 
            display: inline-block;
            text-align: center;
        }
        .badge-success { background: #e3f9e5; color: var(--success); }
        .badge-danger { background: #ffe5e5; color: var(--danger); }

        /* Action Buttons */
        .action-group { display: flex; gap: 10px; justify-content: flex-end; }
        .btn-action { 
            width: 38px; 
            height: 38px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 12px; 
            text-decoration: none; 
            font-size: 15px;
            transition: 0.3s;
        }
        .btn-edit { background: #e3f2fd; color: #1976d2; }
        .btn-edit:hover { background: #1976d2; color: white; }
        
        .btn-delete { background: #fff5f5; color: var(--danger); }
        .btn-delete:hover { background: var(--danger); color: white; }

        /* Responsif Sederhana */
        @media (max-width: 1000px) {
            .table-labels { display: none; }
            .inventory-card { grid-template-columns: 1fr 1fr; gap: 15px; }
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        
        <div class="header-flex">
            <div class="header-title">
                <h2>Inventaris Barang</h2>
                <p>Kelola stok dan aset sarana prasarana sekolah</p>
            </div>
            <a href="tambah_alat.php" class="btn-add">
                <i class="fa-solid fa-plus"></i> Tambah Barang
            </a>
        </div>

        <div class="table-labels">
            <div>Produk</div>
            <div>Nama Alat</div>
            <div>Kategori</div>
            <div>Stok</div>
            <div>Status</div>
            <div style="text-align: right;">Aksi</div>
        </div>

        <div class="inventory-list">
            <?php 
            if(mysqli_num_rows($query_alat) > 0):
                while($row = mysqli_fetch_assoc($query_alat)): 
                    $stok = $row['stok'];
                    $status_label = ($stok > 0) ? 'Tersedia' : 'Habis';
                    $status_class = ($stok > 0) ? 'badge-success' : 'badge-danger';
                    
                    // Cek Foto
                    $foto_path = 'images/' . $row['foto'];
                    $foto = (!empty($row['foto']) && file_exists($foto_path)) ? $row['foto'] : 'default.png';
            ?>
            
            <div class="inventory-card">
                <div class="img-box">
                    <img src="images/<?= $foto ?>" onerror="this.src='images/default.png'">
                </div>

                <div class="item-name"><?= htmlspecialchars($row['nama_alat']) ?></div>

                <div class="category-info">
                    <i class="fa-solid fa-tag" style="font-size: 12px; color: #ccc;"></i> 
                    <?= htmlspecialchars($row['kategori']) ?>
                </div>

                <div class="stok-info">
                    <?= $stok ?> <span style="font-weight:400; color:var(--gray-sub); font-size: 12px;">Unit</span>
                </div>

                <div>
                    <span class="badge <?= $status_class ?>"><?= $status_label ?></span>
                </div>

                <div class="action-group">
                    <a href="edit_alat.php?id=<?= $row['id'] ?>" class="btn-action btn-edit" title="Edit Data">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a href="hapus_alat.php?id=<?= $row['id'] ?>" class="btn-action btn-delete" title="Hapus Data" 
                       onclick="return confirm('Apakah Anda yakin ingin menghapus alat ini?')">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>
            </div>

            <?php 
                endwhile; 
            else:
            ?>
                <div style="text-align: center; padding: 50px; background: white; border-radius: 20px;">
                    <i class="fa-solid fa-box-open" style="font-size: 40px; color: #eee; margin-bottom: 10px;"></i>
                    <p style="color: #aaa;">Belum ada data barang dalam inventaris.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>