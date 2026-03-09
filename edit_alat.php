<?php 
session_start();
include 'config.php'; 

// Proteksi: Izinkan Admin DAN Superadmin masuk
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'petugas' && $_SESSION['role'] != 'superadmin')) { 
    header("Location: index.php"); 
    exit(); 
}

// Pastikan ID ada sebelum query
if (!isset($_GET['id'])) {
    header("Location: stok_barang.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM alat WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (!$data) {
    echo "<script>alert('Data alat tidak ditemukan!'); window.location='stok_barang.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stok | Sarpras Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --telkom-red: #EE2E24;
            --telkom-dark: #2d3436;
            --blue-accent: #3498db;
            --bg-light: #f8fafc;
            --text-main: #334155;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg-light); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh;
            margin: 0;
            color: var(--text-main);
            padding: 20px;
        }

        .form-card { 
            background: white; 
            padding: 40px; 
            border-radius: 24px; 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02); 
            width: 100%;
            max-width: 400px; 
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header { text-align: center; margin-bottom: 25px; }
        
        .photo-preview-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
            border-radius: 20px;
            overflow: hidden;
            border: 3px solid #f1f5f9;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            background: #eee;
        }
        .photo-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        h3 { margin: 0; color: var(--telkom-dark); font-size: 20px; font-weight: 700; }
        p.subtitle { font-size: 13px; color: #94a3b8; margin-top: 5px; }

        .input-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        
        input, select { 
            width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; 
            box-sizing: border-box; font-family: inherit; font-size: 14px; transition: all 0.3s;
        }
        input:focus { outline: none; border-color: var(--blue-accent); box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1); }

        input[type="file"] { padding: 8px; font-size: 12px; border: 2px dashed #e2e8f0; background: #f8fafc; cursor: pointer; }

        .btn-update { 
            background: var(--telkom-red); color: white; border: none; width: 100%; padding: 14px; 
            border-radius: 12px; cursor: pointer; font-weight: 600; font-size: 15px; transition: all 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 10px;
        }
        .btn-update:hover { background: #d6251b; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(238, 46, 36, 0.2); }

        .cancel-link { display: block; text-align: center; margin-top: 20px; font-size: 13px; color: #94a3b8; text-decoration: none; transition: 0.3s; }
        .cancel-link:hover { color: var(--telkom-red); }
    </style>
</head>
<body>

<div class="form-card">
    <div class="header">
        <div class="photo-preview-container">
            <img id="preview" src="images/<?= (!empty($data['foto']) && $data['foto'] != '-') ? $data['foto'] : 'default.png' ?>" onerror="this.src='images/default.png'">
        </div>
        <h3>Edit Detail Barang</h3>
        <p class="subtitle">Update informasi dan stok inventaris</p>
    </div>

    <form action="proses_edit_alat.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">
        
        <div class="input-group">
            <label><i class="fa-solid fa-tag"></i> Nama Alat</label>
            <input type="text" name="nama_alat" value="<?= htmlspecialchars($data['nama_alat']) ?>" required>
        </div>

        <div class="input-group">
            <label><i class="fa-solid fa-layer-group"></i> Kategori</label>
            <select name="kategori">
                <?php 
                $kategori_list = ['Elektronik', 'Olahraga', 'Umum', 'Seni'];
                foreach($kategori_list as $kat): ?>
                    <option value="<?= $kat ?>" <?= $data['kategori'] == $kat ? 'selected' : '' ?>><?= $kat ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="input-group">
            <label><i class="fa-solid fa-boxes-stacked"></i> Jumlah Stok</label>
            <input type="number" name="stok" value="<?= $data['stok'] ?>" min="0" required>
        </div>

        <div class="input-group">
            <label><i class="fa-solid fa-image"></i> Ganti Foto Barang</label>
            <input type="file" name="foto" id="fotoInput" accept="image/*">
            <p style="font-size: 10px; color: #94a3b8; margin-top: 5px;">*Kosongkan jika tidak ingin mengubah foto</p>
        </div>

        <button type="submit" name="update" class="btn-update">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
        </button>
        
        <a href="stok_barang.php" class="cancel-link">Batal dan Kembali</a>
    </form>
</div>

<script>
    // Script simple untuk live preview foto saat dipilih
    document.getElementById('fotoInput').onchange = function (evt) {
        const [file] = this.files
        if (file) {
            document.getElementById('preview').src = URL.createObjectURL(file)
        }
    }
</script>

</body>
</html>