<?php 
session_start();
include 'config.php'; 

// Proteksi: Hanya Superadmin yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') { 
    header("Location: index.php"); 
    exit(); 
}

$notif = "";

// PROSES SIMPAN DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $no_induk = mysqli_real_escape_string($conn, $_POST['no_induk']);
    
    // MENGGUNAKAN TEKS BIASA (Tanpa Hash) sesuai permintaanmu
    $password = mysqli_real_escape_string($conn, $_POST['password']); 
    $role     = $_POST['role'];

    // Cek apakah No Induk sudah ada
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE no_induk = '$no_induk'");
    
    if (mysqli_num_rows($cek) > 0) {
        $notif = "<div class='alert error'>Gagal! No. Induk $no_induk sudah terdaftar.</div>";
    } else {
        $query = "INSERT INTO users (username, no_induk, password, role) VALUES ('$username', '$no_induk', '$password', '$role')";
        
        if (mysqli_query($conn, $query)) {
            // Redirect diarahkan ke user_manajemen.php (Bahasa Indonesia)
            echo "<script>
                alert('Akun berhasil dibuat!'); 
                window.location.href = 'user_manajemen.php';
            </script>";
            exit(); 
        } else {
            $notif = "<div class='alert error'>Terjadi kesalahan: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah User | Sarpras Telkom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --telkom-red: #EE2E24;
            --telkom-dark: #2d3436;
            --white: #ffffff;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: #f4f7f6; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }

        .container {
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { color: var(--telkom-red); margin: 0; font-size: 24px; }
        .header p { color: #636e72; font-size: 14px; }

        label { 
            display: block; 
            font-size: 13px; 
            font-weight: 600; 
            margin-bottom: 8px; 
            color: var(--telkom-dark);
        }

        input, select {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #dfe6e9;
            border-radius: 12px;
            font-family: 'Poppins';
            box-sizing: border-box; 
            transition: 0.3s;
        }

        input:focus { border-color: var(--telkom-red); outline: none; box-shadow: 0 0 0 3px rgba(238, 46, 36, 0.1); }

        .btn-add {
            width: 100%;
            padding: 14px;
            background: var(--telkom-red);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            font-size: 15px;
        }

        .btn-add:hover { background: #d62820; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(238, 46, 36, 0.3); }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #b2bec3;
            font-size: 13px;
        }

        .alert { padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; text-align: center; }
        .error { background: #fff5f5; color: var(--telkom-red); border: 1px solid #ffebeb; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <i class="fa-solid fa-user-shield" style="font-size: 40px; color: var(--telkom-red); margin-bottom: 10px;"></i>
        <h2>Buat Akun Baru</h2>
        <p>Pastikan data No. Induk sesuai dengan kartu identitas.</p>
    </div>

    <?= $notif ?>

    <form action="" method="POST">
        <label><i class="fa-solid fa-user"></i> Nama Lengkap / Username</label>
        <input type="text" name="username" placeholder="Masukkan nama..." required>

        <label><i class="fa-solid fa-id-card"></i> Nomor Induk (NIP/NIS)</label>
        <input type="text" name="no_induk" placeholder="Contoh: 202410123" required>

        <label><i class="fa-solid fa-lock"></i> Password Sementara</label>
        <input type="password" name="password" placeholder="Minimal 6 karakter..." required>

        <label><i class="fa-solid fa-users-gear"></i> Status Akses Akun</label>
        <select name="role" required>
            <option value="peminjam">Siswa (Peminjam)</option>
            <option value="admin">Guru (Admin Sarpras)</option>
            <option value="superadmin">Superadmin</option>
        </select>

        <button type="submit" class="btn-add">
            <i class="fa-solid fa-save"></i> Simpan & Daftarkan Akun
        </button>
        
        <a href="user_manajemen.php" class="btn-back">Batal dan Kembali</a>
    </form>
</div>

</body>
</html>