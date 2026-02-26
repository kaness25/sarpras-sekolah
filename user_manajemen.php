<?php 
session_start();
include 'config.php'; 

// Proteksi Halaman: Hanya Superadmin yang bisa masuk ke manajemen penuh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') { 
    header("Location: index.php"); 
    exit(); 
}

/** * QUERY: Mengambil semua user agar Superadmin bisa memantau semuanya. */
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
        
        /* Sidebar Styling */
        .sidebar { 
            width: 260px; 
            background: var(--white); 
            height: 100vh; 
            position: fixed; 
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 15px rgba(0,0,0,0.05); 
            box-sizing: border-box; 
            z-index: 100;
        }
        
        .sidebar-header { padding: 40px 20px; text-align: center; }
        .sidebar-header img { width: 60px; margin-bottom: 15px; }
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
        }

        .btn-logout {
            display: flex; align-items: center; justify-content: center; width: 100%; padding: 12px;
            background: #fff5f5; color: var(--telkom-red); text-decoration: none; border-radius: 12px;
            font-weight: 700; font-size: 14px; transition: all 0.3s ease; box-sizing: border-box; border: 1px solid #ffebeb;
        }

        .btn-logout:hover {
            background: var(--telkom-red); color: white; box-shadow: 0 4px 12px rgba(238, 46, 36, 0.2); transform: translateY(-2px);
        }

        /* Main Content Styling */
        .main-content { flex: 1; padding: 40px; margin-left: 260px; min-height: 100vh; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }

        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .user-count { background: var(--telkom-light); padding: 5px 15px; border-radius: 20px; font-size: 13px; color: var(--telkom-red); font-weight: 600; }

        .btn { padding: 10px 18px; border-radius: 10px; font-weight: 600; cursor: pointer; border: none; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 11px; }
        .btn-add { background: var(--telkom-red); color: white; box-shadow: 0 4px 15px rgba(238, 46, 36, 0.2); font-size: 12px; }
        
        .btn-edit { background: #eef2ff; color: var(--blue-edit); border: 1px solid #c7d2fe; }
        .btn-edit:hover { background: var(--blue-edit); color: white; }

        .btn-delete { background: #fff5f5; color: var(--telkom-red); border: 1px solid #ffdada; }
        .btn-delete:hover { background: var(--telkom-red); color: white; }
        
        .btn:hover { transform: translateY(-2px); }

        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        th { padding: 15px; color: #b2bec3; font-size: 11px; text-transform: uppercase; text-align: left; letter-spacing: 1px; }
        td { padding: 15px; background: white; border-top: 1px solid #f1f1f1; border-bottom: 1px solid #f1f1f1; transition: 0.2s; }
        
        tr:hover td { background: #fafafa; }
        
        td:first-child { border-left: 1px solid #f1f1f1; border-radius: 12px 0 0 12px; }
        td:last-child { border-right: 1px solid #f1f1f1; border-radius: 0 12px 12px 0; }

        .avatar-circle { width: 35px; height: 35px; background: #dfe6e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #636e72; font-weight: 700; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        .role-badge { padding: 4px 12px; border-radius: 8px; font-size: 10px; font-weight: 700; display: inline-block; }
        .badge-super { background: #6c5ce7; color: white; }
        .badge-admin { background: #fab1a0; color: #d63031; }
        .badge-user { background: #e8f8f5; color: #1abc9c; }

        .action-group { display: flex; gap: 8px; justify-content: center; }
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
            <li><a href="dashboard_superadmin.php"><i class="fa-solid fa-gauge-high"></i> Dashboard SA</a></li>
            <li><a href="user_management.php" class="active"><i class="fa-solid fa-users-gear"></i> Manajemen User</a></li>
        </ul>

        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                <i class="fa-solid fa-power-off" style="margin-right:10px;"></i> Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h3 style="margin:0;"><i class="fa-solid fa-shield-halved" style="color: var(--telkom-red);"></i> Full Access Management</h3>
            <a href="tambah_user.php" class="btn btn-add">
                <i class="fa-solid fa-user-plus"></i> Tambah User Baru
            </a>
        </div>

        <div class="card">
            <div class="table-header">
                <h4 style="margin:0;">Database Pengguna (Semua Level)</h4>
                <span class="user-count"><?= $total_user ?> Akun Sistem</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="50"></th>
                        <th>Username</th>
                        <th>ID / No. Induk</th>
                        <th>Level Akses</th>
                        <th style="text-align: center;">Tindakan</th>
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
                                <div style="font-weight: 600; color: var(--telkom-dark);"><?= htmlspecialchars($u['username']) ?></div>
                                <div style="font-size: 11px; color: #b2bec3;">Terdaftar di Sistem Sarpras</div>
                            </td>
                            <td><code style="color: var(--telkom-red); font-weight: 600;"><?= $u['no_induk'] ?? 'N/A' ?></code></td>
                            <td>
                                <?php 
                                    $role = strtolower($u['role']);
                                    $class = "badge-user";
                                    if($role == 'superadmin') $class = "badge-super";
                                    if($role == 'admin') $class = "badge-admin";
                                ?>
                                <span class="role-badge <?= $class ?>"><?= strtoupper($u['role']) ?></span>
                            </td>
                            <td align="center">
                                <div class="action-group">
                                    <?php if($u['username'] != $_SESSION['username']): ?>
                                        <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-edit" title="Edit Data User">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>

                                        <a href="hapus_user.php?id=<?= $u['id'] ?>" class="btn btn-delete" title="Hapus User Selamanya" onclick="return confirm('Hapus akun ini secara permanen?')">
                                            <i class="fa-solid fa-trash-can"></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size: 10px; color: #b2bec3; font-style: italic;">Akun Anda (Aktif)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" align="center" style="padding: 50px; color: #b2bec3;">Tidak ada user dalam database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>