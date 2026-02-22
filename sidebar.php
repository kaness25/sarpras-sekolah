<?php
// Jangan panggil session_start() lagi di sini jika di file dashboard sudah ada
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? ''; // Mengambil role dari session
?>
<style>
    :root {
        --telkom-red: #EE2E24;
        --telkom-dark: #2d3436;
        --sidebar-width: 260px;
        --bg-light: #f4f7f9;
    }
    body { 
        margin: 0; display: flex; 
        background: var(--bg-light); 
        font-family: 'Poppins', sans-serif; 
    }
    .sidebar {
        width: var(--sidebar-width); height: 100vh; background: #fff;
        position: fixed; left: 0; top: 0; display: flex; flex-direction: column;
        box-shadow: 4px 0 15px rgba(0,0,0,0.05); z-index: 1000;
    }
    .sidebar-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid #f1f1f1; }
    .sidebar-header img { width: 70px; margin-bottom: 10px; }
    .sidebar-header h2 { font-size: 14px; color: var(--telkom-red); margin: 0; font-weight: 700; }
    
    .sidebar-menu { padding: 20px 0; flex-grow: 1; }
    .sidebar-menu a {
        display: flex; align-items: center; padding: 15px 25px; color: #636e72;
        text-decoration: none; font-size: 13px; transition: 0.3s; border-left: 4px solid transparent;
    }
    .sidebar-menu a i { margin-right: 15px; width: 20px; font-size: 16px; }
    .sidebar-menu a:hover, .sidebar-menu a.active {
        background: #fff5f5; color: var(--telkom-red); border-left: 4px solid var(--telkom-red);
    }
    
    .sidebar-footer { padding: 20px; border-top: 1px solid #f1f1f1; }
    .btn-logout-sidebar {
        display: flex; align-items: center; padding: 12px; background: #fff5f5;
        color: var(--telkom-red); text-decoration: none; border-radius: 10px; font-weight: 600; justify-content: center;
        transition: 0.3s;
    }
    .btn-logout-sidebar:hover { background: var(--telkom-red); color: white; }

    /* PENTING: Supaya konten tidak tertutup sidebar */
    .main-content { 
        margin-left: var(--sidebar-width); 
        flex-grow: 1; 
        padding: 40px; 
        width: calc(100% - var(--sidebar-width));
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="images/SMK-TELKOM-MEDAN.png" alt="Logo">
        <h2>SARPRAS DIGITAL</h2>
        <small style="color: #b2bec3; text-transform: uppercase; font-size: 11px; letter-spacing: 2px; display: block; margin-top: 5px; font-weight: 500;">
            <?php echo ($role == 'admin') ? 'PETUGAS' : $role; ?>
        </small>
    </div>
    
    <div class="sidebar-menu">
        <?php if($role == 'superadmin'): ?>
            <a href="dashboard_superadmin.php" class="<?= ($current_page == 'dashboard_superadmin.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge"></i> Dashboard SA
            </a>
        <?php else: ?>
            <a href="dashboard_admin.php" class="<?= ($current_page == 'dashboard_admin.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge"></i> Dashboard
            </a>
        <?php endif; ?>

        <a href="stok_barang.php" class="<?= ($current_page == 'stok_barang.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-boxes-stack"></i> Stok Barang
        </a>
        
        <a href="laporan.php" class="<?= ($current_page == 'laporan.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-invoice"></i> Laporan
        </a>

        <?php if($role == 'superadmin'): ?>
            <a href="user_manajemen.php" class="<?= ($current_page == 'user_manajemen.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-user-gear"></i> Manajemen User
            </a>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout-sidebar" onclick="return confirm('Yakin ingin keluar dari sistem?')">
            <i class="fa-solid fa-power-off"></i> &nbsp; Logout
        </a>
    </div>
</div>