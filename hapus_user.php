<?php
session_start();
include 'config.php';

// 1. Proteksi Keamanan: Hanya Superadmin yang boleh menghapus
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: index.php");
    exit();
}

// 2. Cek apakah ada ID yang dikirim melalui URL
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // 3. Cegah Superadmin menghapus dirinya sendiri
    $username_sekarang = $_SESSION['username'];
    $cek_user = mysqli_query($conn, "SELECT username FROM users WHERE id = '$id'");
    $data = mysqli_fetch_assoc($cek_user);

    if ($data['username'] == $username_sekarang) {
        echo "<script>alert('Anda tidak bisa menghapus akun Anda sendiri!'); window.location='user_manajemen.php';</script>";
        exit();
    }

    // 4. Proses Hapus
    $query_hapus = "DELETE FROM users WHERE id = '$id'";
    
    if (mysqli_query($conn, $query_hapus)) {
        echo "<script>alert('User berhasil dihapus!'); window.location='user_manajemen.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus user: " . mysqli_error($conn) . "'); window.location='user_manajemen.php';</script>";
    }
} else {
    // Jika mencoba akses file ini langsung tanpa ID
    header("Location: user_manajemen.php");
    exit();
}
?>