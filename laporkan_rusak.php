<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$id_p = $_GET['id_p'];

if (isset($_POST['submit_laporan'])) {
    $kerusakan = mysqli_real_escape_string($conn, $_POST['deskripsi_rusak']);
    
    // Update status menjadi 'menunggu_kembali' dan simpan laporannya
    $query = "UPDATE peminjaman SET 
              status_transaksi = 'menunggu_kembali', 
              laporan_kerusakan = '$kerusakan' 
              WHERE id = '$id_p'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Laporan dikirim. Silahkan temui admin untuk cek denda kerusakan.'); window.location='dashboard_user.php';</script>";
    }
}

$res = mysqli_query($conn, "SELECT p.*, a.nama_alat FROM peminjaman p JOIN alat a ON p.alat_id = a.id WHERE p.id = '$id_p'");
$data = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporkan Kerusakan | Sarpras Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --telkom-red: #EE2E24;
            --telkom-dark: #2d3436;
            --warning: #f39c12;
            --bg-light: #f8fafc;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg-light); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }

        .box { 
            background: white; 
            padding: 40px; 
            border-radius: 24px; 
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05); 
            width: 100%;
            max-width: 420px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-icon {
            width: 60px;
            height: 60px;
            background: rgba(238, 46, 36, 0.1);
            color: var(--telkom-red);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 20px;
        }

        h3 { 
            text-align: center; 
            margin: 0 0 10px 0; 
            color: var(--telkom-dark);
            font-weight: 700;
        }

        .info-alat {
            background: #fff9f0;
            border: 1px solid #ffe8cc;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 14px;
            color: #856404;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        textarea { 
            width: 100%; 
            height: 120px; 
            padding: 15px; 
            border-radius: 12px; 
            border: 2px solid #e2e8f0; 
            margin-bottom: 20px; 
            box-sizing: border-box; 
            font-family: inherit;
            font-size: 14px;
            resize: none;
            transition: 0.3s;
        }

        textarea:focus {
            outline: none;
            border-color: var(--telkom-red);
            box-shadow: 0 0 0 4px rgba(238, 46, 36, 0.1);
        }

        .btn-send { 
            background: var(--telkom-red); 
            color: white; 
            border: none; 
            padding: 15px; 
            width: 100%; 
            border-radius: 12px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: 0.3s;
        }

        .btn-send:hover {
            background: #d3271d;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(238, 46, 36, 0.2);
        }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: 0.3s;
        }

        .btn-cancel:hover { color: var(--telkom-dark); }
    </style>
</head>
<body>
    <div class="box">
        <div class="header-icon">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </div>
        <h3>Lapor Kerusakan</h3>
        
        <div class="info-alat">
            Alat: <b><?= $data['nama_alat'] ?></b>
        </div>

        <form method="POST">
            <label>Deskripsi Kerusakan</label>
            <textarea name="deskripsi_rusak" placeholder="Jelaskan secara detail bagian mana yang rusak atau hilang..." required></textarea>
            
            <button type="submit" name="submit_laporan" class="btn-send">
                <i class="fa-solid fa-paper-plane"></i> Kirim Laporan
            </button>
            
            <a href="dashboard_user.php" class="btn-cancel">Batal dan Kembali</a>
        </form>
    </div>
</body>
</html>