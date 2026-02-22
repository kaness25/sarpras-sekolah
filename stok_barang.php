<?php 
session_start();
include 'config.php'; 

// Proteksi halaman admin
if ($_SESSION['role'] != 'admin') { 
    header("Location: index.php"); 
    exit(); 
}

// Ambil data dari database
$query_alat = mysqli_query($conn, "SELECT * FROM alat ORDER BY nama_alat ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Stok Barang | Sarpras Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --telkom-red: #EE2E24; 
            --sidebar-width: 260px; 
            --bg-light: #f8f9fa; 
            --dark: #2d3436;
        }
        
        body { display: flex; background: var(--bg-light); font-family: 'Poppins', sans-serif; margin: 0; color: var(--dark); }
        
        .main-content { margin-left: var(--sidebar-width); flex-grow: 1; padding: 40px; min-height: 100vh; }
        
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03); 
            border: 1px solid rgba(0,0,0,0.05);
        }

        .header-flex { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
        }

        /* Styling Tabel */
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        th { 
            text-align: left; 
            padding: 15px; 
            color: #b2bec3; 
            font-size: 12px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        tr.item-row { background: #fff; transition: 0.3s; }
        td { 
            padding: 15px; 
            vertical-align: middle; 
            border-top: 1px solid #f1f1f1;
            border-bottom: 1px solid #f1f1f1;
        }
        td:first-child { border-left: 1px solid #f1f1f1; border-radius: 12px 0 0 12px; }
        td:last-child { border-right: 1px solid #f1f1f1; border-radius: 0 12px 12px 0; }

        /* Style untuk Foto */
        .img-container {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #eee;
            background: #f9f9f9;
        }
        .img-container img { width: 100%; height: 100%; object-fit: cover; }

        .badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; }
        .badge-success { background: #e3f9e5; color: #1f8b24; }
        .badge-danger { background: #ffe5e5; color: #d63031; }
        
        .btn-add { 
            background: var(--telkom-red); 
            color: white; 
            padding: 12px 24px; 
            border-radius: 12px; 
            text-decoration: none; 
            font-size: 13px; 
            font-weight: 600; 
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(238, 46, 36, 0.2);
            transition: 0.3s;
        }
        .btn-add:hover { transform: translateY(-2px); opacity: 0.9; }

        .action-btns { display: flex; justify-content: center; gap: 10px; }
        .btn-action { 
            width: 35px; 
            height: 35px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 10px; 
            text-decoration: none; 
            font-size: 14px; 
            transition: 0.3s;
        }
        .edit { background: #e3f2fd; color: #1976d2; }
        .delete { background: #fff5f5; color: #e03131; }
        .btn-action:hover { transform: scale(1.1); }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <div class="header-flex">
                <div>
                    <h2 style="margin: 0; font-size: 24px;">Inventaris Barang</h2>
                    <p style="margin: 5px 0 0; color: #aaa; font-size: 13px;">Kelola stok dan aset sarana prasarana</p>
                </div>
                <a href="tambah_alat.php" class="btn-add">
                    <i class="fa-solid fa-plus"></i> Tambah Barang
                </a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="80">Produk</th>
                        <th>Nama Alat</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($query_alat)): 
                        $stok = $row['stok'];
                        $status_label = ($stok > 0) ? 'Tersedia' : 'Habis';
                        $status_class = ($stok > 0) ? 'badge-success' : 'badge-danger';
                        
                        // PERBAIKAN LOGIKA FOTO
                        // Jika kolom foto di DB kosong atau '-', gunakan default
                        $nama_foto = (!empty($row['foto']) && $row['foto'] != '-') ? $row['foto'] : 'default.png';
                        $foto_path = "images/" . $nama_foto;
                    ?>
                    <tr class="item-row">
                        <td>
                            <div class="img-container">
                                <img src="<?= $foto_path ?>" onerror="this.src='images/default.png'">
                            </div>
                        </td>
                        <td>
                            <span style="font-weight: 600; font-size: 15px; color: #333;"><?= $row['nama_alat'] ?></span>
                        </td>
                        <td>
                            <span style="color: #777; font-size: 13px;"><i class="fa-solid fa-tag"></i> <?= $row['kategori'] ?></span>
                        </td>
                        <td>
                            <span style="font-weight: 600;"><?= $stok ?></span> <small color="#aaa">Unit</small>
                        </td>
                        <td>
                            <span class="badge <?= $status_class ?>"><?= $status_label ?></span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="edit_alat.php?id=<?= $row['id'] ?>" class="btn-action edit" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="hapus_alat.php?id=<?= $row['id'] ?>" class="btn-action delete" title="Hapus" onclick="return confirm('Hapus barang ini?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>