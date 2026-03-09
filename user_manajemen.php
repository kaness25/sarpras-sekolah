<?php 
session_start();
include 'config.php'; 

// Proteksi Halaman: Hanya Superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') { 
    header("Location: index.php"); 
    exit(); 
}

/** QUERY: Mengambil semua user */
$query_user = mysqli_query($conn, "SELECT * FROM users ORDER BY role ASC, username ASC");
$total_user = mysqli_num_rows($query_user);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen User | Sarpras Telkom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --telkom-red: #EE2E24;
            --telkom-dark: #2d3436;
            --telkom-light: #f8f9fa;
            --white: #ffffff;
            --blue-edit: #4338ca;
        }

        body { background: #f4f7f9; margin: 0; font-family: 'Poppins', sans-serif; display: flex; }
        
        /* Area Konten Utama */
        .main-content { flex: 1; padding: 40px; min-height: 100vh; }
        
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .card-main { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }

        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .user-count { background: var(--telkom-light); padding: 6px 16px; border-radius: 20px; font-size: 13px; color: var(--telkom-red); font-weight: 600; border: 1px solid #eee; }

        /* Button Styling */
        .btn { padding: 10px 18px; border-radius: 10px; font-weight: 600; cursor: pointer; border: none; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; }
        .btn-add { background: var(--telkom-red); color: white; box-shadow: 0 4px 15px rgba(238, 46, 36, 0.2); }
        .btn-edit { background: #eef2ff; color: var(--blue-edit); border: 1px solid #c7d2fe; }
        .btn-edit:hover { background: var(--blue-edit); color: white; }
        .btn-delete { background: #fff5f5; color: var(--telkom-red); border: 1px solid #ffdada; }
        .btn-delete:hover { background: var(--telkom-red); color: white; }
        .btn:hover { transform: translateY(-2px); }

        /* Table Styling */
        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        th { padding: 15px; color: #b2bec3; font-size: 11px; text-transform: uppercase; text-align: left; letter-spacing: 1px; }
        td { padding: 18px 15px; background: white; border-top: 1px solid #f8f9fa; border-bottom: 1px solid #f8f9fa; transition: 0.2s; }
        tr:hover td { background: #fafafa; border-color: #eee; }
        
        td:first-child { border-left: 1px solid #f8f9fa; border-radius: 15px 0 0 15px; }
        td:last-child { border-right: 1px solid #f8f9fa; border-radius: 0 15px 15px 0; }

        .avatar-circle { width: 40px; height: 40px; background: #dfe6e9; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #636e72; font-weight: 700; font-size: 18px; border: 2px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        
        .role-badge { padding: 5px 12px; border-radius: 8px; font-size: 10px; font-weight: 800; display: inline-block; letter-spacing: 0.5px; }
        .badge-super { background: #6c5ce7; color: white; }
        .badge-admin { background: #fab1a0; color: #d63031; } /* Warna Merah */
        .badge-user { background: #e8f8f5; color: #1abc9c; }

        .action-group { display: flex; gap: 10px; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="top-bar">
            <h3 style="margin:0;"><i class="fa-solid fa-shield-halved" style="color: var(--telkom-red); margin-right: 10px;"></i> Full Access Management</h3>
            <a href="tambah_user.php" class="btn btn-add">
                <i class="fa-solid fa-user-plus"></i> Tambah User Baru
            </a>
        </div>

        <div class="card-main">
            <div class="table-header">
                <h4 style="margin:0; font-weight: 600;">Database Pengguna (Semua Level)</h4>
                <span class="user-count"><?= $total_user ?> Akun Terdaftar</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="60"></th>
                        <th>Username</th>
                        <th>ID / No. Induk</th>
                        <th>Level Akses</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($total_user > 0): ?>
                        <?php while($u = mysqli_fetch_assoc($query_user)): ?>
                        <tr>
                            <td>
                                <div class="avatar-circle">
                                    <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--telkom-dark); font-size: 15px;"><?= htmlspecialchars($u['username']) ?></div>
                                <div style="font-size: 11px; color: #b2bec3;">Aktif dalam sistem</div>
                            </td>
                            <td><code style="color: var(--telkom-red); font-weight: 700; background: #fff5f5; padding: 4px 8px; border-radius: 5px;"><?= $u['no_induk'] ?? 'N/A' ?></code></td>
                            <td>
                                <?php 
                                    $role = strtolower($u['role']);
                                    $class = "badge-user";
                                    
                                    if($role == 'superadmin') {
                                        $class = "badge-super";
                                    } elseif($role == 'admin' || $role == 'petugas') { 
                                        // Level PETUGAS sekarang menggunakan class warna merah (badge-admin)
                                        $class = "badge-admin"; 
                                    }
                                ?>
                                <span class="role-badge <?= $class ?>"><?= strtoupper($u['role']) ?></span>
                            </td>
                            <td>
                                <div class="action-group">
                                    <?php if($u['username'] != $_SESSION['username']): ?>
                                        <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-edit" title="Edit User">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="hapus_user.php?id=<?= $u['id'] ?>" class="btn btn-delete" title="Hapus User" onclick="return confirm('Hapus akun ini secara permanen?')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: #b2bec3; font-style: italic; background: #f8f9fa; padding: 5px 10px; border-radius: 8px;">Ini Akun Anda</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" align="center" style="padding: 50px; color: #b2bec3;">Belum ada user yang ditambahkan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>