<?php
include 'config.php';

if(isset($_POST['tambah'])){
    // Menggunakan Prepared Statement agar lebih aman dari SQL Injection
    $nama   = $_POST['nama_alat'];
    $kat    = $_POST['kategori'];
    $stok   = (int)$_POST['stok']; 

    // --- PERBAIKAN DISINI ---
    // Sesuaikan dengan ENUM di database kamu. 
    // Berdasarkan kode dashboard sebelumnya, gunakan 'ready' bukan 'available'
    $status = ($stok > 0) ? 'ready' : 'kosong'; 

    // Menyiapkan query
    $stmt = mysqli_prepare($conn, "INSERT INTO alat (nama_alat, kategori, status, stok) VALUES (?, ?, ?, ?)");
    
    // Pastikan urutan bind_param sesuai: nama(s), kategori(s), status(s), stok(i)
    mysqli_stmt_bind_param($stmt, "sssi", $nama, $kat, $status, $stok);
    
    if(mysqli_stmt_execute($stmt)){
        header("Location: dashboard_admin.php?pesan=berhasil");
        exit();
    } else {
        // Jika masih error, ini akan menampilkan pesan detail dari MySQL
        $error_msg = "Gagal Simpan: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Alat | Sarpras</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #EE2E24;
            --primary-dark: #d3271d;
            --bg: #f8fafc;
            --text: #334155;
            --gray: #64748b;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh;
            margin: 0;
        }

        .form-card { 
            background: white; 
            padding: 40px; 
            border-radius: 24px; 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); 
            width: 100%;
            max-width: 420px; 
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header i { 
            background: rgba(238, 46, 36, 0.1); 
            color: var(--primary); 
            padding: 20px; 
            border-radius: 50%; 
            font-size: 30px; 
            margin-bottom: 15px;
        }

        h3 { color: var(--text); margin: 0; font-weight: 600; font-size: 22px; }
        .subtitle { color: var(--gray); font-size: 14px; margin-top: 5px; }

        .input-group { margin-bottom: 20px; }
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-size: 14px; 
            font-weight: 500; 
            color: var(--text); 
        }

        input, select { 
            width: 100%; 
            padding: 12px 16px; 
            border: 2px solid #e2e8f0; 
            border-radius: 12px; 
            box-sizing: border-box; 
            font-family: 'Poppins';
            transition: all 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(238, 46, 36, 0.1);
        }

        .btn-submit { 
            background: var(--primary); 
            color: white; 
            border: none; 
            width: 100%; 
            padding: 14px; 
            border-radius: 12px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 16px;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover { 
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px rgba(238, 46, 36, 0.2);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--gray);
            text-decoration: none;
            transition: 0.3s;
        }

        .back-link:hover { color: var(--primary); }
        
        .alert {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>

<div class="form-card">
    <div class="header">
        <i class="fa-solid fa-plus-circle"></i>
        <h3>Tambah Alat</h3>
        <p class="subtitle">Input data inventaris sarpras baru</p>
    </div>

    <?php if(isset($error_msg)): ?>
        <div class="alert"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label>Nama Alat</label>
            <input type="text" name="nama_alat" placeholder="Misal: Proyektor BenQ" required>
        </div>
        
        <div class="input-group">
            <label>Kategori</label>
            <select name="kategori" required>
                <option value="" disabled selected>-- Pilih Kategori --</option>
                <option value="Elektronik">Elektronik</option>
                <option value="Olahraga">Olahraga</option>
                <option value="Seni">Seni</option>
                <option value="Umum">Umum</option>
            </select>
        </div>

        <div class="input-group">
            <label>Jumlah Stok</label>
            <input type="number" name="stok" min="1" value="1" required>
        </div>

        <button type="submit" name="tambah" class="btn-submit">
            <i class="fa-solid fa-save"></i> Simpan ke Stok
        </button>
        
        <a href="dashboard_admin.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </form>
</div>

</body>
</html>