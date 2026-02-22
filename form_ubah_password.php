<?php
session_start();
include 'config.php';

// Proteksi: Hanya Superadmin yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') { 
    header("Location: index.php"); 
    exit(); 
}

$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT id, username, role FROM users WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika form disubmit
if (isset($_POST['update_password'])) {
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];

    if ($password_baru === $konfirmasi) {
        // Enkripsi password (sangat disarankan menggunakan password_hash)
        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
        
        // Update ke database
        $update = mysqli_query($conn, "UPDATE users SET password = '$hashed_password' WHERE id = '$id'");
        
        if ($update) {
            header("Location: user_management.php?pesan=reset_berhasil");
            exit();
        }
    } else {
        $error = "Konfirmasi password tidak cocok!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password | Sarpras Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --telkom-red: #EE2E24;
            --telkom-dark: #2d3436;
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
            max-width: 400px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
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

        h3 { text-align: center; margin: 0; color: var(--telkom-dark); font-weight: 700; }
        p.user-info { text-align: center; color: #94a3b8; font-size: 14px; margin-bottom: 30px; }

        .error-msg {
            background: #fff5f5;
            color: var(--telkom-red);
            padding: 10px;
            border-radius: 10px;
            font-size: 12px;
            text-align: center;
            margin-bottom: 20px;
            border: 1px solid #ffdada;
        }

        label { display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 8px; }

        .input-group { margin-bottom: 20px; position: relative; }
        
        input { 
            width: 100%; 
            padding: 12px 16px; 
            border: 2px solid #e2e8f0; 
            border-radius: 12px; 
            box-sizing: border-box; 
            font-size: 14px;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--telkom-red);
            box-shadow: 0 0 0 4px rgba(238, 46, 36, 0.1);
        }

        .btn-save { 
            background: var(--telkom-dark); 
            color: white; 
            border: none; 
            padding: 15px; 
            width: 100%; 
            border-radius: 12px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 15px;
            transition: 0.3s;
        }

        .btn-save:hover { background: #000; transform: translateY(-2px); }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <div class="box">
        <div class="header-icon">
            <i class="fa-solid fa-key"></i>
        </div>
        <h3>Ubah Password</h3>
        <p class="user-info">Mengganti password untuk user: <b><?= htmlspecialchars($data['username']) ?></b></p>

        <?php if(isset($error)): ?>
            <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Password Baru</label>
                <input type="password" name="password_baru" placeholder="Masukkan password baru" required autofocus>
            </div>

            <div class="input-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="konfirmasi_password" placeholder="Ulangi password baru" required>
            </div>

            <button type="submit" name="update_password" class="btn-save">
                Simpan Password Baru
            </button>
            
            <a href="user_management.php" class="btn-back">Batal</a>
        </form>
    </div>

</body>
</html>