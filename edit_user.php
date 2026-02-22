<?php 
session_start();
include 'config.php'; 

// Proteksi Halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') { 
    header("Location: index.php"); 
    exit(); 
}

// Pastikan ID ada
if (!isset($_GET['id'])) {
    header("Location: user_manajemen.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (!$data) {
    header("Location: user_manajemen.php");
    exit();
}

// Proses Update
if (isset($_POST['update'])) {
    // trim() digunakan untuk menghapus spasi di awal/akhir agar tidak kena 'Data Truncated'
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $no_induk = mysqli_real_escape_string($conn, trim($_POST['no_induk']));
    $role     = mysqli_real_escape_string($conn, trim($_POST['role'])); 
    $password = $_POST['password'];

    if (!empty($password)) {
        // Jika password diisi
        $query_update = "UPDATE users SET username='$username', no_induk='$no_induk', role='$role', password='$password' WHERE id='$id'";
    } else {
        // Jika password kosong
        $query_update = "UPDATE users SET username='$username', no_induk='$no_induk', role='$role' WHERE id='$id'";
    }

    if (mysqli_query($conn, $query_update)) {
        echo "<script>
                alert('Data user berhasil diperbarui!'); 
                window.location='user_manajemen.php';
              </script>";
    } else {
        // Menampilkan pesan error yang lebih jelas jika gagal
        echo "Gagal memperbarui: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Sarpras</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card-edit { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #636e72; margin-bottom: 8px; }
        input, select { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #dfe6e9; box-sizing: border-box; font-family: 'Poppins'; font-size: 14px; }
        .btn-save { width: 100%; padding: 12px; background: #EE2E24; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        .btn-save:hover { background: #d3271d; transform: translateY(-2px); }
        .btn-back { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #b2bec3; font-size: 13px; }
    </style>
</head>
<body>

<div class="card-edit">
    <h3 style="margin-top:0; color: #2d3436;"><i class="fa-solid fa-user-gear" style="color: #EE2E24;"></i> Edit Pengguna</h3>
    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>
        </div>
        <div class="form-group">
            <label>No. Induk (NIS/NIP)</label>
            <input type="text" name="no_induk" value="<?= htmlspecialchars($data['no_induk']) ?>">
        </div>
        <div class="form-group">
            <label>Role / Level</label>
            <select name="role" required>
                <option value="user" <?= ($data['role'] == 'user') ? 'selected' : '' ?>>USER (Siswa)</option>
                <option value="admin" <?= ($data['role'] == 'admin') ? 'selected' : '' ?>>ADMIN (Staff)</option>
                <option value="superadmin" <?= ($data['role'] == 'superadmin') ? 'selected' : '' ?>>SUPERADMIN</option>
            </select>
        </div>
        <div class="form-group">
            <label>Password Baru</label>
            <input type="password" name="password" placeholder="Kosongkan jika tidak diganti">
        </div>
        <button type="submit" name="update" class="btn-save">Simpan Perubahan</button>
        <a href="user_manajemen.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </form>
</div>

</body>
</html>