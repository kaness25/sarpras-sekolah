<?php 
session_start();
include 'config.php'; 

// 1. CEK SESSION (Jika sudah login, langsung lempar ke dashboard yang sesuai)
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'superadmin') {
        header("Location: dashboard_superadmin.php");
    } 
    // PERBAIKAN: Petugas diarahkan ke dashboard admin
    elseif ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'petugas') {
        header("Location: dashboard_admin.php");
    } 
    else {
        header("Location: dashboard_user.php");
    } 
    exit();
}

// 2. PROSES LOGIN
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        
        // Simpan data ke Session
        $_SESSION['user_id']  = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role']     = $data['role'];

        // REDIRECT BERDASARKAN ROLE
        if ($data['role'] == 'superadmin') {
            header("Location: dashboard_superadmin.php");
        } 
        // PERBAIKAN: Petugas diarahkan ke dashboard admin
        elseif ($data['role'] == 'admin' || $data['role'] == 'petugas') {
            header("Location: dashboard_admin.php");
        } 
        else {
            header("Location: dashboard_user.php");
        }
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sarpras SMK Telkom Medan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --telkom-red: #EE2E24;
            --telkom-dark: #2d3436;
            --telkom-gray: #f4f7f6;
        }

        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body { 
            background: var(--telkom-gray);
            background-image: radial-gradient(circle at 20% 20%, rgba(238, 46, 36, 0.05) 0%, transparent 40%),
                              radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.05) 0%, transparent 40%);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            overflow: hidden;
        }

        .bg-decoration {
            position: absolute;
            width: 500px;
            height: 500px;
            background: var(--telkom-red);
            filter: blur(80px);
            opacity: 0.03;
            border-radius: 50%;
            z-index: -1;
            top: -100px;
            right: -100px;
        }

        .login-box { 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 24px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.08); 
            width: 100%;
            max-width: 420px; 
            text-align: center;
            position: relative;
            border: 1px solid rgba(0,0,0,0.03);
        }

        .school-logo {
            width: 100px; 
            height: auto;
            margin-bottom: 15px;
        }

        h2 { 
            color: var(--telkom-dark); 
            margin: 0; 
            font-weight: 700; 
            font-size: 20px; 
            letter-spacing: -0.5px;
        }

        .subtitle { 
            color: #b2bec3; 
            margin-bottom: 30px; 
            font-size: 13px; 
            font-weight: 400;
        }

        .input-group { margin-bottom: 20px; text-align: left; }
        
        label { 
            font-size: 11px; 
            color: var(--telkom-dark); 
            font-weight: 700; 
            margin-left: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input { 
            width: 100%; 
            padding: 12px 16px; 
            margin-top: 8px;
            border: 2px solid #edf2f7; 
            border-radius: 12px; 
            outline: none; 
            transition: all 0.3s ease;
            background: #fdfdfd;
            font-size: 14px;
        }

        input:focus { 
            border-color: var(--telkom-red); 
            background: #fff;
            box-shadow: 0 0 0 4px rgba(238, 46, 36, 0.1);
        }

        button { 
            width: 100%; 
            padding: 15px; 
            background: var(--telkom-red); 
            color: white; 
            border: none; 
            border-radius: 12px; 
            cursor: pointer; 
            font-size: 15px; 
            font-weight: 700; 
            transition: all 0.3s;
            margin-top: 10px;
            letter-spacing: 1px;
            box-shadow: 0 8px 20px rgba(238, 46, 36, 0.2);
        }

        button:hover { 
            background: #d6281f;
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(238, 46, 36, 0.3);
        }

        .error { 
            background: #fff5f5; 
            color: var(--telkom-red); 
            padding: 12px; 
            border-radius: 12px; 
            font-size: 13px; 
            margin-bottom: 20px; 
            border-left: 4px solid var(--telkom-red);
            text-align: left;
        }

        .footer-info { 
            font-size: 11px; 
            color: #b2bec3; 
            margin-top: 30px; 
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="bg-decoration"></div>
    
    <div class="login-box">
        <img src="images/SMK-TELKOM-MEDAN.png" alt="Logo SMK Telkom Medan" class="school-logo">
        
        <h2>SARPRAS DIGITAL</h2>
        <p class="subtitle">SMK Telkom Medan Inventory System</p>
        
        <?php if(isset($error)): ?>
            <div class="error">
                <strong>Gagal:</strong> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan ID User" required autocomplete="off">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" name="login">LOGIN KE SISTEM</button>
        </form>

        <div class="footer-info">
            &copy; 2026 SMK Telkom Medan.<br>
            Developing Excellence Through Technology.
        </div>
    </div>
</body>
</html>